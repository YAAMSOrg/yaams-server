<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TwoFactorReset;
use App\Support\ActivityLevel;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

/**
 * Account recovery for users who lost both their authenticator and their
 * recovery codes: removes two-factor authentication so they can sign in with
 * their password only, and informs them by e-mail.
 */
class ResetTwoFactorCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yaams:reset-2fa
                            {email : The e-mail address of the user}
                            {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes two-factor authentication from a user account and informs the user by e-mail.';

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'email' => 'What is the e-mail address of the user?',
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with the e-mail address {$email}.");

            return self::FAILURE;
        }

        if (is_null($user->two_factor_secret)
            && is_null($user->two_factor_recovery_codes)
            && is_null($user->two_factor_confirmed_at)) {
            $this->info("{$user->name} ({$user->email}) has no two-factor authentication set up. Nothing to do.");

            return self::SUCCESS;
        }

        $this->info("Name: {$user->name}");
        $this->info("E-mail: {$user->email}");
        $this->info('2FA confirmed at: '.($user->two_factor_confirmed_at?->toDateTimeString() ?? 'not confirmed (setup pending)'));

        if (! $this->option('force')
            && ! $this->confirm('Remove two-factor authentication from this account? The user will be informed by e-mail.', false)) {
            $this->info('Aborted, nothing was changed.');

            return self::FAILURE;
        }

        // Cleared directly instead of via Fortify's DisableTwoFactorAuthentication:
        // its TwoFactorAuthenticationDisabled event is logged by AuthEventSubscriber
        // as caused by the user themselves, which would misrepresent who did this.
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        activity()
            ->performedOn($user)
            ->withProperties(['level' => ActivityLevel::WARNING, 'via' => 'console'])
            ->event('two_factor_reset')
            ->log('Two-factor authentication reset by an instance admin');

        $user->notify(new TwoFactorReset);

        $this->info('Two-factor authentication removed. The user can now sign in with their password only.');
        $this->info('The notification e-mail is queued and is sent by the queue worker.');

        return self::SUCCESS;
    }
}

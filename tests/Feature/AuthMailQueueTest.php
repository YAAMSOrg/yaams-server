<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Auth mails (email verification, password reset) are queued, so a slow or
 * failing SMTP server can never fail registration or the reset request.
 */
class AuthMailQueueTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        // Any existing user marks the instance as set up, so /register is reachable.
        User::factory()->create();
        Queue::fake();
    }

    public function test_registration_queues_the_verification_mail(): void
    {
        $this->post(route('register'), [
            'name' => 'New Pilot',
            'email' => 'new@test.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'new@test.com']);
        Queue::assertPushed(SendQueuedNotifications::class, fn ($job) => $job->notification instanceof VerifyEmail);
    }

    public function test_password_reset_request_queues_the_reset_mail(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertRedirect();

        Queue::assertPushed(SendQueuedNotifications::class, fn ($job) => $job->notification instanceof ResetPassword);
    }
}

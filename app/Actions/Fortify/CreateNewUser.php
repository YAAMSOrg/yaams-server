<?php

namespace App\Actions\Fortify;

use App\Models\Setting;
use App\Models\User;
use App\Support\LegalPage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        // Defend the registration endpoint even if the form is bypassed —
        // the instance-wide toggle lives in the settings table.
        abort_if(Setting::get('allow_registration', '1') !== '1', 403, 'Registration is currently closed.');

        Validator::make($input, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // The privacy policy (and the terms, when published) - see LegalPage.
            'accept_legal' => ['accepted'],
        ], [
            'accept_legal.accepted' => LegalPage::enabled(LegalPage::TERMS)
                ? 'You must accept the privacy policy and the terms of use.'
                : 'You must accept the privacy policy.',
        ])->validate();

        $user = new User([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Not fillable: proof of consent is only ever set here.
        $user->forceFill(['legal_accepted_at' => now()])->save();

        return $user;
    }
}

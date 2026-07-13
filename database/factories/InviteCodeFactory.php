<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InviteCode>
 */
class InviteCodeFactory extends Factory
{
    protected $model = InviteCode::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('???-####')),
            'airline_id' => Airline::factory(),
            'created_by' => User::factory(),
            'role' => 'Pilot',
            'used_by' => null,
            'used_at' => null,
        ];
    }

    /**
     * Mark the code as already redeemed by a user.
     */
    public function usedBy(User $user): static
    {
        return $this->state(fn () => [
            'used_by' => $user->id,
            'used_at' => now(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aircraft>
 */
class AircraftFactory extends Factory
{
    protected $model = Aircraft::class;

    public function definition(): array
    {
        return [
            'registration' => strtoupper($this->faker->unique()->bothify('?-????')),
            'manufacturer' => $this->faker->randomElement(['Airbus', 'Boeing', 'Embraer']),
            'model' => $this->faker->randomElement(['A320', '737-800', 'E195']),
            // current_loc is a NOT NULL FK to airports.icao_code, so an airport
            // must exist. Create one unless a test pins the location via at().
            'current_loc' => fn () => Airport::factory()->create()->icao_code,
            'used_by' => Airline::factory(),
            'status' => Aircraft::STATUS_ACTIVE,
        ];
    }

    /**
     * Park the airframe at a specific, already-existing airport.
     */
    public function at(string $icao): static
    {
        return $this->state(fn () => ['current_loc' => strtoupper($icao)]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Aircraft::STATUS_INACTIVE]);
    }

    public function retired(): static
    {
        return $this->state(fn () => [
            'status' => Aircraft::STATUS_RETIRED,
            'retired_at' => now(),
            'retired_reason' => 'End of lease',
        ]);
    }
}

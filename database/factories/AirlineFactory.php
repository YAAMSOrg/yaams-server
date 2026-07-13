<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Airline>
 */
class AirlineFactory extends Factory
{
    protected $model = Airline::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Airlines',
            'prefix' => strtoupper($this->faker->unique()->lexify('??')),
            'icao_callsign' => strtoupper($this->faker->unique()->lexify('???')),
            'atc_callsign' => strtoupper($this->faker->word()),
            'country' => $this->faker->countryCode(),
            'unit_is_lbs' => false,
            'active' => true,
            // Matches the founding/setup default: review is on, continuity is off.
            'require_pirep_review' => true,
            'location_continuity' => false,
        ];
    }

    /**
     * Opt into location-continuity realism mode.
     */
    public function locationContinuity(): static
    {
        return $this->state(fn () => ['location_continuity' => true]);
    }

    /**
     * PIREPs are auto-accepted; no reviewer queue.
     */
    public function noReview(): static
    {
        return $this->state(fn () => ['require_pirep_review' => false]);
    }
}

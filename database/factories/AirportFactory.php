<?php

namespace Database\Factories;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Airport>
 */
class AirportFactory extends Factory
{
    protected $model = Airport::class;

    public function definition(): array
    {
        return [
            'icao_code' => strtoupper($this->faker->unique()->lexify('????')),
            'name' => $this->faker->city() . ' Airport',
            'latitude_deg' => $this->faker->latitude(),
            'longitude_deg' => $this->faker->longitude(),
            'elevation_ft' => $this->faker->numberBetween(0, 9000),
            'iso_country' => $this->faker->countryCode(),
            'iata_code' => strtoupper($this->faker->unique()->lexify('???')),
        ];
    }

    /**
     * Create an airport with a fixed ICAO code and coordinates. Useful when a
     * test needs a specific, referenceable location (e.g. Haversine distance).
     */
    public function icao(string $icao, float $lat = null, float $lon = null): static
    {
        return $this->state(fn () => array_filter([
            'icao_code' => strtoupper($icao),
            'latitude_deg' => $lat,
            'longitude_deg' => $lon,
        ], fn ($value) => $value !== null));
    }
}

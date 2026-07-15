<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\AircraftImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AircraftImage>
 */
class AircraftImageFactory extends Factory
{
    protected $model = AircraftImage::class;

    public function definition(): array
    {
        return [
            'aircraft_id' => Aircraft::factory(),
            'path' => fn (array $attrs) => 'aircraft/'.($attrs['aircraft_id'] ?? 0).'/'.Str::uuid().'.webp',
            'is_primary' => false,
            'uploaded_by' => null,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}

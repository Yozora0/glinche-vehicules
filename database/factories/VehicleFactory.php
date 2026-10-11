<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'reference' => (string) $this->faker->unique()->numberBetween(100000, 999999),
            'title' => 'Renault Clio TCe 90 Evolution',
            'brand' => 'Renault',
            'model' => 'Clio',
            'version' => 'TCe 90 Evolution',
            'offer_type' => 'VO',
            'energy' => 'ES',
            'gearbox' => 'MAN',
            'mileage' => $this->faker->numberBetween(0, 150000),
            'year' => $this->faker->numberBetween(2018, 2026),
            'power' => 90,
            'price' => $this->faker->numberBetween(9000, 45000),
            'main_picture_url' => 'https://example.com/photo.jpg',
            'pictures' => ['https://example.com/photo.jpg'],
            'synced_at' => now(),
        ];
    }
}

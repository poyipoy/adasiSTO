<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'name' => 'Rack '.fake()->unique()->bothify('##??'),
            'old_location_name' => null,
            'description' => null,
            'warehouse' => null,
            'is_active' => true,
            'created_by_user_id' => null,
        ];
    }
}

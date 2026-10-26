<?php

namespace Database\Factories;

use App\Models\MasterMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterMaterialFactory extends Factory
{
    protected $model = MasterMaterial::class;

    public function definition(): array
    {
        return [
            'material_code' => strtoupper(fake()->unique()->bothify('#?')),
            'material_name' => 'Material '.fake()->unique()->bothify('###??'),
            'is_active' => true,
        ];
    }
}

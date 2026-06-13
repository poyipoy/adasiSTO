<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        $plants = ['Cikarang', 'Deltamas', 'Surabaya'];

        foreach ($plants as $plantName) {
            Plant::updateOrCreate(
                ['name' => $plantName],
                ['is_active' => true]
            );
        }
    }
}

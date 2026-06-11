<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        $plants = [
            ['code' => 'CKR', 'name' => 'Cikarang'],
            ['code' => 'DLT', 'name' => 'Deltamas'],
            ['code' => 'SBY', 'name' => 'Surabaya'],
        ];

        foreach ($plants as $plant) {
            Plant::create($plant);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            StoCodeSeeder::class,
            PlantSeeder::class,
            MasterMaterialSeeder::class,
            MasterKeteranganSeeder::class,
        ]);
    }
}

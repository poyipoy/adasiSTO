<?php

namespace Database\Seeders;

use App\Models\MasterMaterial;
use Illuminate\Database\Seeder;

class MasterMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['code' => '1H', 'name' => 'SKD11'],
            ['code' => '2P', 'name' => 'SKD61'],
            ['code' => '2L', 'name' => 'DHAW'],
            ['code' => '4F', 'name' => 'P20'],
            ['code' => '4E', 'name' => 'NAK80'],
            ['code' => '1B', 'name' => 'DC53'],
        ];

        foreach ($materials as $material) {
            MasterMaterial::create($material);
        }
    }
}

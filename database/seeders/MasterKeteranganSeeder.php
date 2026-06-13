<?php

namespace Database\Seeders;

use App\Models\MasterKeterangan;
use Illuminate\Database\Seeder;

class MasterKeteranganSeeder extends Seeder
{
    public function run(): void
    {
        $keterangan = [
            ['name' => 'OK'],
            ['name' => 'Lot Salah'],
            ['name' => 'Size Salah'],
            ['name' => 'Material Salah'],
        ];

        foreach ($keterangan as $item) {
            MasterKeterangan::updateOrCreate(
                ['name' => $item['name']],
                ['is_active' => true]
            );
        }
    }
}

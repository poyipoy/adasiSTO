<?php

namespace Database\Seeders;

use App\Models\StoCode;
use Illuminate\Database\Seeder;

class StoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $stoCodes = [
            [
                'code' => 'STO2606',
                'description' => 'STO Juni 2026',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
                'is_active' => true,
            ],
            [
                'code' => 'STO2607',
                'description' => 'STO Juli 2026',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
                'is_active' => false,
            ],
        ];

        foreach ($stoCodes as $stoCode) {
            StoCode::updateOrCreate(
                ['code' => $stoCode['code']],
                $stoCode
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Admin STO',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'is_validator' => true,
        ]);

        User::updateOrCreate(['username' => 'operator1'], [
            'name' => 'Operator 1',
            'password' => Hash::make('password'),
            'role' => 'scanner',
            'is_active' => true,
            'is_validator' => false,
        ]);

        User::updateOrCreate(['username' => 'operator2'], [
            'name' => 'Operator 2',
            'password' => Hash::make('password'),
            'role' => 'scanner',
            'is_active' => true,
            'is_validator' => false,
        ]);
    }
}

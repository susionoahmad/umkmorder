<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kurnia Telur Users (tenant_id = 1)
        User::create([
            'tenant_id' => 1,
            'name' => 'Ahmad Owner',
            'email' => 'owner1@kurniatelur.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        User::create([
            'tenant_id' => 1,
            'name' => 'Budi Staff',
            'email' => 'staff1@kurniatelur.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // 2. Sambal Bu Sari Users (tenant_id = 2)
        User::create([
            'tenant_id' => 2,
            'name' => 'Sari Owner',
            'email' => 'owner2@sambalbusari.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        User::create([
            'tenant_id' => 2,
            'name' => 'Siti Staff',
            'email' => 'staff2@sambalbusari.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}

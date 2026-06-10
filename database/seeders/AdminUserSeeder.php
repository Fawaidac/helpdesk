<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        User::truncate(); // Hapus semua data lama (opsional, tergantung kebutuhan)
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('soebandi'),
            ]
        );
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@mail.com',
                'password' => Hash::make('rahasia'),
                'role' => 'superadmin',
                'pin' => Hash::make('875306'),
            ]
        );
        User::updateOrCreate(
            ['username' => 'user'],
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('rahasia'),
                'role' => 'user',
            ]
        );
    }
}

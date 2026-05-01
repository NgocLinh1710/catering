<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Quản trị viên',
            'email' => 'admin@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }
}

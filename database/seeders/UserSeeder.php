<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => env('ADMIN_EMAIL', 'admin@example.com'),
                'is_admin' => true,
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}

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
                'name' => 'Ryan Boc',
                'email' => env('ADMIN_EMAIL', 'developer@inglewoofarms.com'),
                'is_admin' => true,
                'password' => Hash::make(env('ADMIN_PASSWORD', '88888888')),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}

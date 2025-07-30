<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'テスト太郎',
            'email' => 'test1@example.com',
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => 'サンプル花子',
            'email' => 'test2@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}


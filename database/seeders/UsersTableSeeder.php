<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'مFadi',
            'email' => 'fadi@gmail.com',
            'password' => Hash::make('12345678'), // كلمة المرور مشفرة
        ]);

        
    }
}
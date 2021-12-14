<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'=>'admin',
            'username'=>'admin',
            'phone'=>'09384902913',
            'verified'=>true,
            'role'=>'admin',
            'password'=>bcrypt('admin1234'),
            'telegram_code' => Str::random(40),
        ]);

        User::create([
            'name'=>'user',
            'username'=>'user',
            'phone'=>'00000000000',
            'verified'=>true,
            'role'=>'user',
            'password'=>bcrypt('12345678'),
            'telegram_code' => Str::random(40),
        ]);
    }
}

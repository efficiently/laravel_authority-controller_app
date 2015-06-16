<?php

use Illuminate\Database\Seeder;
use App\User;

class UserTableSeeder extends Seeder
{
    public function run()
    {
        // !!! All existing users are deleted !!!
        DB::table('users')->truncate();

        User::create([
            'email'     => 'admin@localhost.com',
            'name'  => 'Administrator',
            'password'  => Hash::make('password'),
        ]);
    }
}

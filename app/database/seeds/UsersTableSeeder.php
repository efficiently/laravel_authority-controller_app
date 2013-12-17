<?php

class UsersTableSeeder extends Seeder {

    public function run()
    {
        // !!! All existing users are deleted !!!
        DB::table('users')->truncate();

        User::create([
            'email'     => 'admin@localhost',
            'name'  => 'Administrator',
            'password'  => Hash::make('password'),
        ]);
    }
}

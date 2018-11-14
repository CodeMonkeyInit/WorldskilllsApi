<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "admin",
            //TODO get bearer token size
            'token' => str_random(50),
            'password' => bcrypt('sakhalin2018'),
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                "name" => "User 1",
                "email" => "user1@assetlog.jaapps.com",
                "password" => bcrypt("password")
            ],[
                "name" => "Admin",
                "email" => "admin@assetlog.jaapps.com",
                "password" => bcrypt("password"),
                "is_admin" => 1 // admin user
            ],
        ];

        foreach ($users as $user)
        {
            \App\Models\User::create($user);
        }
    }
}

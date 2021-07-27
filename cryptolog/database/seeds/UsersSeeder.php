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
                "name" => "William Odiomonafe",
                "email" => "william.odiomonafe@gmail.com",
                "password" => bcrypt("password")
            ],
        ];

        foreach ($users as $user)
        {
            \App\Models\User::create($user);
        }
    }
}

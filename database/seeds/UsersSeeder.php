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
        $users = [[
                "name" => "Admin",
                "email" => "admin@assetlog.co",
                "password" => bcrypt("@ssetl0g"),
                "is_admin" => 1 // admin user
            ],
        ];

        foreach ($users as $user)
        {
            \App\Models\User::create($user);
        }
    }
}

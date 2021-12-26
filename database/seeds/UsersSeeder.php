<?php

use App\Models\User;
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
        if (!User::exists()) {
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
}

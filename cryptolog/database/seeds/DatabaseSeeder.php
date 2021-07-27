<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        \Illuminate\Support\Facades\Artisan::call('passport:client --personal');

         $this->call(UsersSeeder::class);
         $this->call(PlatformsSeeder::class);
         $this->call(AssetTypesSeeder::class);
         $this->call(AssetsSeeder::class);
         $this->call(AssetLogsSeeder::class);
    }
}

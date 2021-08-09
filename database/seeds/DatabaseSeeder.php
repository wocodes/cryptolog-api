<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('passport:install');

         $this->call(UsersSeeder::class);
         $this->call(PlatformsSeeder::class);
         $this->call(ExternalApiSeeder::class);
         $this->call(AssetTypesSeeder::class);
         $this->call(AssetsSeeder::class);
         $this->call(AssetLogsSeeder::class);
    }
}

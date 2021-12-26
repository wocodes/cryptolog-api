<?php

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Platform::exists()) {
            $platforms = [
                ["name" => "Binance"],
                ["name" => "Trove"]
            ];

            foreach ($platforms as $platform) {
                \App\Models\Platform::create($platform);
            }
        }
    }
}

<?php

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
        $platforms = [
            ["name" => "Binance"],
            ["name" => "Trove"]
        ];

        foreach ($platforms as $platform)
        {
            \App\Models\Platform::create($platform);
        }
    }
}

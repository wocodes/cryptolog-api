<?php

use Illuminate\Database\Seeder;

class AssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $assets = [
            ["name" => "Bitcoin", "symbol" => "BTC"],
            ["name" => "Ethereum", "symbol" => "ETH"]
        ];

        foreach ($assets as $asset)
        {
            \App\Models\Asset::create($asset);
        }
    }
}

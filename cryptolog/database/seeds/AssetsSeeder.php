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
            [
                "name" => "Bitcoin",
                "symbol" => "BTC",
                "asset_type_id" => \App\Models\AssetType::where('name', 'Cryptocurrency')->first()->id
            ],
            [
                "name" => "Ethereum",
                "symbol" => "ETH",
                "asset_type_id" => \App\Models\AssetType::where('name', 'Cryptocurrency')->first()->id
            ]
        ];

        foreach ($assets as $asset)
        {
            \App\Models\Asset::create($asset);
        }
    }
}

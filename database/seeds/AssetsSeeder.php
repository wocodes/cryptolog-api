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
        $cryptocurrencyAssetType = \App\Models\AssetType::where('name', 'Cryptocurrency')->first();
        $assets = [
            [
                "name" => "Bitcoin",
                "symbol" => "BTC",
                "asset_type_id" => $cryptocurrencyAssetType->id
            ],
            [
                "name" => "Ethereum",
                "symbol" => "ETH",
                "asset_type_id" => $cryptocurrencyAssetType->id
            ],
            [
                "name" => "Dogecoin",
                "symbol" => "DOGE",
                "asset_type_id" => $cryptocurrencyAssetType->id
            ]
        ];

        foreach ($assets as $asset)
        {
            $asset = \App\Models\Asset::create($asset);

            $asset->platforms()->attach(['platform_id' => 1]);
        }
    }
}

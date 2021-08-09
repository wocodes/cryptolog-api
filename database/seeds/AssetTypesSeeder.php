<?php

use Illuminate\Database\Seeder;

class AssetTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activeCryptoApi = \App\Models\ExternalApi::whereJsonContains('meta->tags', "cryptocurrency")
            ->where('active', 1)
            ->first();

        $activeStockApi = \App\Models\ExternalApi::whereJsonContains('meta->tags', "stock")
            ->where('active', 1)
            ->first();

        $types = [
            ["name" => "Cryptocurrency", "external_api_id" => $activeCryptoApi->id],
            ["name" => "Stock", "external_api_id" => $activeStockApi->id]
        ];

        foreach ($types as $type)
        {
            \App\Models\AssetType::create($type);
        }
    }
}

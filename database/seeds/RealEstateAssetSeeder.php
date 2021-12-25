<?php

use App\Models\Asset;
use App\Models\AssetType;
use Illuminate\Database\Seeder;

class RealEstateAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $realEstateAssetType = AssetType::firstOrCreate(['name' => 'Real Estate']);

        if(!Asset::where('asset_type_id', $realEstateAssetType->id)->exists()) {
            $assets = [
              ["name" => "Vacant Land"],
              ["name" => "Residential"],
              ["name" => "Commercial"],
              ["name" => "Industrial"],
            ];

            $realEstateAssetType->assets()->create($assets);
        }
    }
}

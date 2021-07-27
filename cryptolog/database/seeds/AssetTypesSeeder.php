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
        $types = [
            ["name" => "Cryptocurrency"],
            ["name" => "Stock"]
        ];

        foreach ($types as $type)
        {
            \App\Models\AssetType::create($type);
        }
    }
}

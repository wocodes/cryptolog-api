<?php

use Illuminate\Database\Seeder;

class AssetLogsSeeder extends Seeder
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
                "user_id" => \App\Models\User::first()->id,
                "asset_type_id" => \App\Models\AssetType::first()->id,
                "platform_id" => \App\Models\Platform::first()->id,
                "asset_id" => \App\Models\Asset::first()->id,
                "quantity_bought" => 0.00026547,
                "initial_value" => 23.73,
                "current_value" => 14.14,
                "profit_loss" => 9.59,
                "24_hr_change" => 5.589,
                "date_bought" => \Carbon\Carbon::today()->subDays(rand(2, 10)),
                "roi" => 40.39,
                "daily_roi" => 0.64,
                "current_price" => 31702,
                "last_updated_at" => now(),
                "profit_loss_naira" => 9.59*500
            ],
        ];

        foreach ($assets as $asset)
        {
            \App\Models\AssetLog::create($asset);
        }
    }
}

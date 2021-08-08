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
                "platform_id" => \App\Models\Platform::first()->id,
                "asset_id" => \App\Models\Asset::first()->id,
                "quantity_bought" => 0.00026547,
                "initial_value" => 23.73,
                "current_value" => 0.0,
                "profit_loss" => 0.0,
                "24_hr_change" => 0.0,
                "date_bought" => \Carbon\Carbon::today()->subDays(rand(2, 10)),
                "roi" => 0.0,
                "daily_roi" => 0.0,
                "current_price" => 0.0,
                "last_updated_at" => now(),
                "profit_loss_naira" => 0.0
            ],
        ];

        foreach ($assets as $asset)
        {
            \App\Models\AssetLog::create($asset);
        }
    }
}

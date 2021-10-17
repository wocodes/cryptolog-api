<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class AssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = "database/assets.sql";
        \Illuminate\Support\Facades\DB::unprepared(file_get_contents($path));
    }



    public function oldRunner()
    {
        $user = User::findOrFail(1)->apiKeys()->first();
        $timestamp = Carbon::now()->getTimestampMs();
        $url = "https://api.binance.com/api/v3/account";

        $queryString = "timestamp=$timestamp";
        $signature = hash_hmac("sha256", $queryString, $user->secret);
        $url .= "?$queryString&signature=$signature";

        try {
            $assets = Http::withHeaders(["X-MBX-APIKEY" => $user->key])->get($url)->json();

            $cryptocurrencyAssetType = \App\Models\AssetType::where('name', 'Cryptocurrency')->first();
            foreach ($assets['balances'] as $asset)
            {
                $asset = \App\Models\Asset::create([
                    "name" => $asset['asset'],
                    "symbol" => $asset['asset'],
                    "asset_type_id" => $cryptocurrencyAssetType->id
                ]);

                $asset->platforms()->attach(['platform_id' => 1]);
            }
        } catch (\Illuminate\Http\Client\RequestException $requestException) {
            throw new \Exception($requestException->getMessage());
        }
    }

}

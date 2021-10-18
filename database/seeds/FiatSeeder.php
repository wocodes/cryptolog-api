<?php

use App\Models\Fiat;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FiatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
//            $externalRates = Cache::get('external_fiats_data', function () {
            $externalRates = Http::get('https://api.remitano.com/api/v1/rates/ads')->json();
//            });

            Fiat::create([
                'name' => 'Naira',
                'country_code' => 'ng',
                'symbol' => $externalRates['ng']['currency'],
                'usdt_sell_rate' => $externalRates['ng']['usdt_bid'],
                'usdt_buy_rate' => $externalRates['ng']['usdt_ask']
            ]);

        } catch (RequestException $e) {
            throw new RequestException("An error fetching fiats");
        }
    }
}

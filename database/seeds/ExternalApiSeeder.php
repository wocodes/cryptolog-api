<?php

use App\Models\ExternalApi;
use Illuminate\Database\Seeder;

class ExternalApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!ExternalApi::exists()) {
            $apis = [
                [
                    "organization" => "Binance",
                    "host" => "https://api3.binance.com/api/v3",
                    "meta" => ["tags" => "cryptocurrency"],
                    "active" => 1
                ],
                [
                    "organization" => "YahooFinance",
                    "host" => "https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v2",
                    "meta" => [
                        "tags" => "stock",
                        "ticker_format" => "https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v2/get-summary?symbol=symbol_pair&region=US",
                        "headers" => [
                            "x-rapidapi-key" => "1b7e88d308mshd70d50cd922d241p11e98fjsn15c57e365314",
                            "x-rapidapi-host" => "apidojo-yahoo-finance-v1.p.rapidapi.com"
                        ]
                    ],
                    "active" => 1
                ]
            ];

            foreach ($apis as $api) {
                \App\Models\ExternalApi::create($api);
            }
        }
    }
}

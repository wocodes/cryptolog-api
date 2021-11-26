<?php

use App\Models\User;
use Binance\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//const STATUS_CODES = [
//    "OK" => 200,
//    "Created" => 201,
//    "Unauthorized" => 401,
//    "Bad Request" => 400,
//    "Forbidden" => 403,
//    "Not Found" => 404,
//    "Internal Server Error" => 500,
//];


require_once 'apis/admin.php';
require_once 'apis/users.php';

require 'apis/assets.php';

require 'apis/logs.php';
require_once 'apis/platforms.php';
require_once 'apis/fiat.php';

Route::post("waitlist", "\App\Actions\Waitlist\Add");

Route::get('test', function() {
//    $apiKey = User::first()->apiKeys()->first();
//
//    $api = new API($apiKey->key, $apiKey->secret);
    $api = new API();
//    $api->chart(["BTCUSDT"], "15m", function($api, $symbol, $chart) {
//        echo "{$symbol} chart update\n";
//        print_r($chart);
//    });

    // MA (5)
    $ticks = $api->candlesticks("SHIBUSDT", "15m", 6);
    $closingPrices = array_column($ticks, 'close');
    array_pop($closingPrices);
    $theMA = number_format(array_sum($closingPrices) / count($closingPrices), 8);
    dump("MA(5): $theMA");
//    4.5796E-5

    // MA (10)
    $ticks = $api->candlesticks("SHIBUSDT", "15m", 11);
    $closingPrices = array_column($ticks, 'close');
    array_pop($closingPrices);
    $theMA = number_format(array_sum($closingPrices) / count($closingPrices), 8);
    dd("MA(10): $theMA");
});

<?php

use App\Actions\Assets\Logs\UpdateCryptoAssetValue;
use App\Actions\Assets\Logs\UpdateRealEstateAssetValue;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("asset-logs")->group(function ($router) {
    Route::get('/', '\App\Actions\Assets\Logs\GetLogs');
    Route::post('/', '\App\Actions\Assets\Logs\CreateLog');
    $router->get('/update', function () {
        (new UpdateRealEstateAssetValue)->handle();
        (new UpdateCryptoAssetValue)->handle();
    });
    $router->post('/{id}/withdrawal', '\App\Actions\Assets\Logs\CreateWithdrawal');
});

Route::middleware('auth:api')->prefix("bot-logs")->group(function ($router) {
    Route::get('/', '\App\Actions\Bot\Trading\Crypto\GetLogs');
});



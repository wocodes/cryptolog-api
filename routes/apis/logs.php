<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("logs")->group(function ($router) {
    Route::get('/', '\App\Actions\Assets\Logs\GetLogs');
    Route::post('/', '\App\Actions\Assets\Logs\CreateLog');
    $router->get('/crypto/update', '\App\Actions\Assets\Logs\UpdateCryptoAssetValue');
    $router->get('/real-estate/update', '\App\Actions\Assets\Logs\UpdateRealEstateAssetValue');
    $router->post('/{id}/withdrawal', '\App\Actions\Assets\Logs\CreateWithdrawal');
});



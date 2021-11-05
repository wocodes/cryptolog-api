<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("assets")->group(function ($router) {
    $router->get('/', '\App\Actions\Assets\ListAssets');
    $router->get('/types', '\App\Actions\AssetTypes\ReadAll');
    $router->put('/log/{id}/sold', '\App\Actions\Assets\Logs\Sold');
    $router->get('/report/earnings-summary', '\App\Actions\Assets\Report\EarningsSummary');
});

Route::middleware(['admin', 'auth:api'])->prefix("admin/assets")->group(function () {
    Route::get('/', '\App\Actions\Admin\Asset\ListAsset');
    Route::post('/', '\App\Actions\Admin\Asset\CreateAsset');
});



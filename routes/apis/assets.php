<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("assets")->group(function ($router) {
    $router->get('/', '\App\Actions\Assets\ListAssets');
    $router->get('/types', '\App\Actions\AssetTypes\ReadAll');
    $router->put('/log/{id}/sold', '\App\Actions\Assets\Logs\Sold');
    $router->get('/report/earnings-summary/{asset_type?}', '\App\Actions\Assets\Report\EarningsSummary');
    $router->get('/locations', '\App\Actions\Assets\Locations\GetLocation');
    $router->post('/bot-trade/activate', '\App\Actions\Assets\BotTrade\ActivateBotTrade');
    $router->post('/bot-trade/deactivate', '\App\Actions\Assets\BotTrade\DeactivateBotTrade');
    $router->get('/bot-trade/status', '\App\Actions\Assets\BotTrade\CheckStatus');
});

Route::middleware(['admin', 'auth:api'])->prefix("admin/assets")->group(function () {
    Route::get('/', '\App\Actions\Admin\Asset\ListAsset');
    Route::post('/', '\App\Actions\Admin\Asset\CreateAsset');
});



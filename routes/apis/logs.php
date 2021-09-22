<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("logs")->group(function ($router) {
    $router->get('/', '\App\Actions\Assets\Logs\GetLogs');
    $router->post('/', '\App\Actions\Assets\Logs\CreateLog');
    $router->get('/update', '\App\Actions\Assets\Logs\UpdateAssetLogs');
    $router->post('/{id}/withdrawal', '\App\Actions\Assets\Logs\CreateWithdrawal');
});



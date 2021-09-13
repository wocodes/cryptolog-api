<?php

use Illuminate\Support\Facades\Route;

$router->put('/log/{id}/sold', '\App\Actions\Assets\Logs\Sold');

$router->get('/', '\App\Actions\Assets\Get');
$router->get('/types', '\App\Actions\AssetTypes\ReadAll');
$router->get('/report/earnings-summary', '\App\Actions\Assets\Report\EarningsSummary');

$router->middleware('admin')->group(function ($router) {
    $router->get('/', '\App\Actions\Admin\Asset\ListAsset');
    $router->post('/', '\App\Actions\Admin\Asset\CreateAsset');
});


<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("fiats")->group(function ($router) {
    $router->get('/', '\App\Actions\Shared\Fiat\ListFiats');
});

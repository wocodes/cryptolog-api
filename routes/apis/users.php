<?php
use Illuminate\Support\Facades\Route;

Route::prefix("user")->group(function() {
    Route::post("/register", '\App\Actions\User\Auth\Register');
    Route::post('/login', '\App\Actions\User\Auth\Login');

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/', '\App\Actions\User\GetUser');
        Route::get('/dashboard/stats', '\App\Actions\User\Dashboard\Stats');
        Route::get('/metas', '\App\Actions\User\Metas\Get');
        Route::post('/metas', '\App\Actions\User\Metas\Save');

        Route::put('/', '\App\Actions\User\Update');
        Route::post('/api-keys', '\App\Actions\User\SaveApiKeys');
    });
});

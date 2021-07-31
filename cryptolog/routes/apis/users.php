<?php

Route::prefix("user")->group(function() {
    Route::post("/register", '\App\Actions\User\Auth\Register');
    Route::post('/login', '\App\Actions\User\Auth\Login');

    Route::get('/', '\App\Actions\User\GetUser')->middleware('auth:api');
    Route::get('/dashboard/stats', '\App\Actions\User\Dashboard\Stats')->middleware('auth:api');
});

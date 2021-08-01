<?php

use Illuminate\Support\Facades\Route;

Route::prefix("platforms")->group(function() {
    Route::get('/', '\App\Actions\Platforms\GetAll')->middleware('auth:api');
//    Route::post('/', '')->middleware('auth:api');
//    Route::delete('/', '')->middleware('auth:api');
});

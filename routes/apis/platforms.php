<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("platforms")->group(function() {
    Route::get('/', '\App\Actions\Platforms\Get');
//    Route::post('/', '')->middleware('auth:api');
//    Route::delete('/', '')->middleware('auth:api');
});

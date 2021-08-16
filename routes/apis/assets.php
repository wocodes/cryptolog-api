<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix("assets")->group(function() {
    Route::get('/', '\App\Actions\Assets\Get');
    Route::get('/types', '\App\Actions\AssetTypes\ReadAll');
//    Route::post('/', '')->middleware('auth:api');
//    Route::delete('/', '')->middleware('auth:api');


    Route::get('/report/earnings-summary', '\App\Actions\Assets\Report\EarningsSummary');
});


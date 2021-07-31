<?php

Route::middleware('auth:api')->prefix("assets")->group(function() {
    Route::get('/', '\App\Actions\Assets\GetAll');
//    Route::post('/', '')->middleware('auth:api');
//    Route::delete('/', '')->middleware('auth:api');


    Route::get('/report/earnings-summary', '\App\Actions\Assets\Report\EarningsSummary');
});


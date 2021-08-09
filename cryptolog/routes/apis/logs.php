<?php

Route::prefix("logs")->group(function() {
    Route::get('/', '\App\Actions\Assets\Logs\TopPerforming')->middleware('auth:api');
    Route::post('/', '\App\Actions\Assets\Logs\Create')->middleware('auth:api');
    Route::get('/update', '\App\Actions\Assets\Logs\UpdateAssetLogs')->middleware('auth:api');
});



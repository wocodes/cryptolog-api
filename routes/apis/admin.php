<?php

use Illuminate\Support\Facades\Route;

Route::middleware('admin')->prefix("assets")->group(function () {
    Route::get('/', '\App\Actions\Admin\Asset\CreateAsset');
    Route::post('/ownership/', '\App\Actions\Admin\Asset\Ownership\CreateNewAsset');
});


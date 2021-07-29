<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//const STATUS_CODES = [
//    "OK" => 200,
//    "Created" => 201,
//    "Unauthorized" => 401,
//    "Bad Request" => 400,
//    "Forbidden" => 403,
//    "Not Found" => 404,
//    "Internal Server Error" => 500,
//];


Route::prefix("user")->group(function() {
    Route::post("/register", '\App\Actions\User\Auth\Register');
    Route::post('/login', '\App\Actions\User\Auth\Login');

    Route::get('/', '\App\Actions\User\GetUser')->middleware('auth:api');
});

Route::prefix("logs")->group(function() {
    Route::get('/', '\App\Actions\Assets\Logs\GetLogs')->middleware('auth:api');
    Route::get('/update', '\App\Actions\Assets\Logs\UpdateAssetLogs')->middleware('auth:api');
});


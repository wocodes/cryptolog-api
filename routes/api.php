<?php

use App\Models\User;
use Binance\API;
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


require_once 'apis/admin.php';
require_once 'apis/users.php';

require 'apis/assets.php';

require 'apis/logs.php';
require_once 'apis/platforms.php';
require_once 'apis/fiat.php';

Route::post("waitlist", "\App\Actions\Waitlist\Add");

Route::get('test', function() {});

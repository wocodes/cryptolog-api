<?php

use App\Models\User;
use Illuminate\Http\Request;

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


require 'apis/users.php';
require 'apis/assets.php';
require 'apis/logs.php';
require 'apis/platforms.php';

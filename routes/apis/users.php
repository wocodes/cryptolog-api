<?php
use Illuminate\Support\Facades\Route;

Route::prefix("user")->group(function() {
    Route::post("/register", '\App\Actions\User\Auth\Register');
    Route::post('/login', '\App\Actions\User\Auth\Login');
    Route::post('/reset-password', '\App\Actions\User\Auth\ResetPassword');

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/', '\App\Actions\User\GetUser');
        Route::get('/dashboard/stats', '\App\Actions\User\Dashboard\Stats');
        Route::get('/metas', '\App\Actions\User\Metas\Get');
        Route::post('/metas', '\App\Actions\User\Metas\Save');

        Route::put('/', '\App\Actions\User\Update');
        Route::post('/api-keys', '\App\Actions\User\SaveApiKeys');
    });
});


Route::middleware(['admin', 'auth:api'])->prefix("admin/users")->group(function () {
    Route::get('/send-invite/{id}', '\App\Actions\Admin\User\SendInvite');
    Route::get('/waitlist', '\App\Actions\Admin\User\GetWaitlist');
});

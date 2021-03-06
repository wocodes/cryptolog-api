<?php
use Illuminate\Support\Facades\Route;

Route::prefix("user")->group(function() {
    Route::post("/register", '\App\Actions\User\Auth\Register');
    Route::post('/login', '\App\Actions\User\Auth\Login');
    Route::post('/reset-password', '\App\Actions\User\Auth\ResetPassword');

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/', '\App\Actions\User\GetUser');
        Route::get('/dashboard/stats/{asset_type?}', '\App\Actions\User\Dashboard\Stats');
        Route::get('/metas', '\App\Actions\User\Metas\Get');
        Route::post('/metas', '\App\Actions\User\Metas\Save');

        Route::put('/', '\App\Actions\User\Update');
        Route::post('/api-keys', '\App\Actions\User\SaveApiKeys');

        // wallet transactions
        Route::get('wallet', '\App\Actions\User\Wallet\GetWallet');
        Route::post('wallet/credit', '\App\Actions\User\Wallet\CreditWallet');

        // complete setup
        Route::get('complete-setup', function () {
            $user = auth()->user();
            $user->finished_setup = 1;
            $user->save();

            return response()->json([], 200);
        });
    });
});


Route::middleware(['admin', 'auth:api'])->prefix("admin/users")->group(function () {
    Route::get('/send-invite/{id}', '\App\Actions\Admin\User\SendInvite');
    Route::get('/waitlist', '\App\Actions\Admin\User\GetWaitlist');
});

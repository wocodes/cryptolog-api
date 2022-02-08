<?php

namespace App\Listeners;

use App\Models\Fiat;
use App\Models\Wallet;
use App\Notifications\SendVerificationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AcknowledgeRegistration
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $user = $event->user;
        // send verification email
        $user->notifyNow(new SendVerificationEmail());

        // set user role and permission
        $user->assignRole(Role::findByName('client', 'api'));

        $wallet = $user->wallet;

//        $this->processUserFreeSubscription($user);

    }

    protected function processUserFreeSubscription($user)
    {
        $wallet = $this->creditUserWalletWithFreeSubscription($user);

        $this->debitUserForFreeSubscription($wallet);
    }

    protected function debitUserForFreeSubscription($wallet)
    {
        $walletBalance = $wallet->current_balance;
        $wallet->current_balance = 0;
        $wallet->save();

        $wallet->transaction()->create([
            'transaction_reference' => md5(now()),
            'description' => "wallet debit for 3 months subscription",
            'value' => $walletBalance,
            'status' => 1
        ]);
    }

    protected function creditUserWalletWithFreeSubscription($user)
    {
        $ngnFiatBuyRate = Fiat::where('country_code', 'ng')->first()->usdt_buy_rate;
        $numFreeMonthsSubscription = 3;
        $totalValue = (env('MONTHLY_SUBSCRIPTION_FEE_USD') * $ngnFiatBuyRate) * $numFreeMonthsSubscription;

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'current_balance' => $totalValue
        ]);

        $wallet->transaction()->create([
            'transaction_reference' => md5(now()),
            'description' => "free wallet credit",
            'value' => $totalValue,
            'status' => 1
        ]);

        return $wallet;
    }
}

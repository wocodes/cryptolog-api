<?php

namespace App\Listeners;

use App\Models\Wallet;
use App\Notifications\SendVerificationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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

        if ($wallet) {
            $wallet->current_balance += 3000;
            $wallet->save();
        } else {
            Wallet::create([
                'user_id' => $user->id,
                'current_balance' => 3000
            ]);
        }
    }
}

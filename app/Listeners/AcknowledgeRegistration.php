<?php

namespace App\Listeners;

use App\Actions\User\AppSubscription\SaveNewSubscription;
use App\Actions\User\Permissions\GrantPermission;
use App\Models\Fiat;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\SendVerificationEmail;
use Carbon\Carbon;
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

        // Grant user FREE 3 months subscription
        $this->grantFreeSubscription($user->id);

    }

    protected function grantFreeSubscription(int $userId)
    {

        $permissions = GrantPermission::run(['all_permissions' => true, 'user_id' => $userId]);

        if (count($permissions)) {
            $subscriptionEndDate = Carbon::now()->addMonths(env('NUMBER_OF_FREE_SUBSCRIPTION_MONTHS'));

            SaveNewSubscription::run([
                'start_date' => now(),
                'end_date' => $subscriptionEndDate,
                "active" => 1,
                'user_id' => $userId
            ]);
        }
    }
}

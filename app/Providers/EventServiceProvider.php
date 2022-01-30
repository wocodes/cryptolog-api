<?php

namespace App\Providers;

use App\Actions\Assets\Logs\ImportNewAssetsFromBinance;
use App\Events\ApiKeysSaved;
use App\Events\RegistrationSuccessful;
use App\Listeners\AcknowledgeRegistration;
use App\Listeners\ImportAssetsFromBinance;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class
        ],

    RegistrationSuccessful::class => [
        AcknowledgeRegistration::class
    ],


//        ApiKeysSaved::class => [
//            ImportNewAssetsFromBinance::class, 'handle'
//        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

//        Telescope::filter(function (IncomingEntry $entry) {
//            if ($this->app->environment('local')) {
//                return true;
//            }
//
//            return $entry->isReportableException() ||
//                   $entry->isFailedRequest() ||
//                   $entry->isFailedJob() ||
//                   $entry->isScheduledTask() ||
//                   $entry->hasMonitoredTag();
//        });

        Telescope::tag(function (IncomingEntry $entry) {
            if ($entry->type === 'log') {
                $message = $entry->content['message'];

                // Check if the log message starts with a square bracket
                if (strpos($message, '[') === 0) {
                    // Extract the tag that's inside the square brackets
                    preg_match('/\[(.*?)\]/', $message, $tag);
                    return [$tag[1]];
                }
            }

            return [];
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        return;

        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'admin@assetlog.co'
            ]);
        });
    }
}

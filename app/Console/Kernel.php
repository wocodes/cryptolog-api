<?php

namespace App\Console;

use App\Actions\Assets\Logs\ImportNewAssetsFromBinance;
use App\Actions\Assets\Logs\UpdateAssetLogs;
use App\Actions\Assets\Logs\UpdateAssetValue;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $chunkedCollection = User::all()->chunk(100);

        foreach ($chunkedCollection as $item) {
            foreach ($item as $user) {
                $schedule->job(ImportNewAssetsFromBinance::run(['user_id' => $user]))->hourly();
                $schedule->job(UpdateAssetLogs::run(['user_id' => $user]))->everySixHours();
                $schedule->job(UpdateAssetValue::run(['user_id' => $user]))->everySixHours();
            }
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

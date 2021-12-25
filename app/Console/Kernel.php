<?php

namespace App\Console;

use App\Actions\Assets\Logs\ImportNewAssetsFromBinance;
use App\Actions\Assets\Logs\UpdateAssetLogs;
use App\Actions\Assets\Logs\UpdateAssetValue;
use App\Actions\Bot\Trading\Crypto\GetCallToAction;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

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
//        $schedule->call(function() {
//            $chunkedCollection = User::where('is_admin', 0)->get()->chunk(50);
//            foreach ($chunkedCollection as $item) {
//                foreach ($item as $user) {
//                    ImportNewAssetsFromBinance::run(['user_id' => $user->id]);
//                    UpdateAssetLogs::run(['user_id' => $user->id]);
//                    UpdateAssetValue::run(['user_id' => $user->id]);
//                }
//            }
//        })->hourly();

        $schedule->job(new GetCallToAction())->everyMinute();
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

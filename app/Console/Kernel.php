<?php

namespace App\Console;

use App\Actions\Assets\Logs\ImportNewAssetsFromBinance;
use App\Actions\Assets\Logs\UpdateAssetLogs;
use App\Actions\Assets\Logs\UpdateCryptoAssetValue;
use App\Actions\Assets\Logs\UpdateRealEstateAssetValue;
use App\Actions\Bot\Trading\Crypto\GetCallToAction;
use App\Models\User;
use Binance\API;
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
        $schedule->call(function () {
            $chunkedCollection = User::where('is_admin', 0)->get()->chunk(50);
            foreach ($chunkedCollection as $item) {
                foreach ($item as $user) {
                    ImportNewAssetsFromBinance::run(['user_id' => $user->id]);
                    UpdateAssetLogs::run(['user_id' => $user->id]);
                    UpdateCryptoAssetValue::run(['user_id' => $user->id]);
                }
            }
        })->hourly();


        $schedule->command('db:seed --class=FiatSeeder')->twiceDaily();

        $schedule->call(function () {
            $chunkedCollection = User::where('is_admin', 0)->get()->chunk(50);
            foreach ($chunkedCollection as $item) {
                foreach ($item as $user) {
                    UpdateRealEstateAssetValue::run(['user_id' => $user->id]);
                }
            }
        })->daily();



        $schedule->job(new GetCallToAction())->everyFiveMinutes();

//        $schedule->call(function () {
//            $api = new API('LbcvrB9bNo0yY2Am7FlqKGW7qY2P9BMApf4dWe2CgZoTTZ0m5r46aSPkboP4nNPt', 'TUoM5G9ArPctiQPOS6YutMXVskPXOGPpYMDfLaKggQodgIPAQGXb4rf4Xm6Za5Dt');
//            $data = $api->MarketSellTest("SHIBUSDT", 530613);
//
//            dd($data);
//        })->everyMinute();
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

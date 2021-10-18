<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProfitLossNairaToProfitLossFiatOnAssetLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->renameColumn('profit_loss_naira', 'profit_loss_fiat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profit_loss_fiat_on_asset_logs', function (Blueprint $table) {
            $table->renameColumn('profit_loss_fiat', 'profit_loss_naira');
        });
    }
}

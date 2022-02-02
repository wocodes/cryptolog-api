<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotTradeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_trade_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_trades')->constrained();
            $table->double('value_bought');
            $table->double('qty_bought');
            $table->double('value_sold')->nullable();
            $table->double('qty_sold')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_trade_logs');
    }
}

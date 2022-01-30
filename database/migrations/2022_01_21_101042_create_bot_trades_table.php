<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('asset_id')->nullable()->constrained();
            $table->enum('mode', ['auto', 'manual'])->default('auto');
            $table->boolean('is_active')->default(0);
            $table->double('initial_value')->default(0)->comment('value in usd');
            $table->double('current_value')->default(0)->comment('value in usd');
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
        Schema::dropIfExists('ai_trades');
    }
}

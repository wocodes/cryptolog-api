<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('platform_id')->nullable();
            $table->unsignedBigInteger('asset_id');
            $table->decimal('quantity_bought', 16, 8);
            $table->decimal('initial_value', 16, 2);
            $table->decimal('current_value', 16, 2)->default(0.0);
            $table->decimal('profit_loss', 16, 2)->default(0.0);
            $table->decimal('24_hr_change', 16, 8)->default(0.0);
            $table->boolean('status')->default(1);
            $table->dateTime('date_bought');
            $table->decimal('roi', 16, 2)->default(0.0);
            $table->decimal('daily_roi', 16, 8)->default(0.0);
            $table->decimal('current_price', 16, 2)->default(0.0);
            $table->dateTime('last_updated_at')->default(Carbon\Carbon::now());
            $table->decimal('profit_loss_naira', 16, 2)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->foreign('platform_id')->references('id')->on('platforms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_logs');
    }
}

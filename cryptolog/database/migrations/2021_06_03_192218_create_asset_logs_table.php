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
            $table->unsignedBigInteger('asset_type_id');
            $table->unsignedBigInteger('platform_id')->nullable();
            $table->unsignedBigInteger('asset_id');
            $table->decimal('quantity_bought', 8, 8);
            $table->decimal('initial_value');
            $table->decimal('current_value');
            $table->decimal('profit_loss');
            $table->decimal('24_hr_change');
            $table->boolean('status')->default(1);
            $table->dateTime('date_bought');
            $table->decimal('roi');
            $table->decimal('daily_roi');
            $table->decimal('current_price');
            $table->dateTime('last_updated_at');
            $table->decimal('profit_loss_naira')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->foreign('asset_type_id')->references('id')->on('asset_types');
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_ownership_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('transaction_id');
            $table->double('sub_units');
            $table->boolean('is_subscribed')->default(0);
            $table->double('initial_value')->nullable();
            $table->double('current_value')->nullable();
            $table->double('daily_growth_value')->nullable()->comment('value at which asset appreciates daily');
            $table->boolean('is_sold')->default(0);
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
        Schema::dropIfExists('asset_subscriptions');
    }
}

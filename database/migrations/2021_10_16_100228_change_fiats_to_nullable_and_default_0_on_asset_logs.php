<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFiatsToNullableAndDefault0OnAssetLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->decimal('current_value_fiat', 16)->nullable(false)->default(0.00)->change();
            $table->decimal('initial_value_fiat', 16)->nullable(false)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->decimal('current_value_fiat', 16)->nullable()->change();
            $table->decimal('initial_value_fiat', 16)->nullable()->change();
        });
    }
}

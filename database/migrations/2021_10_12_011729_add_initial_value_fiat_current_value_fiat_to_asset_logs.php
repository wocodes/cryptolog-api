<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInitialValueFiatCurrentValueFiatToAssetLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->decimal('initial_value_fiat', 16)->after('initial_value')->nullable();
            $table->decimal('current_value_fiat', 16)->after('current_value')->nullable();
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
            $table->dropColumn('initial_value_fiat', 16);
            $table->dropColumn('current_value_fiat', 16);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiatShotSymbolToFiats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fiats', function (Blueprint $table) {
            $table->string('short_symbol')->nullable()->after('symbol');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fiats', function (Blueprint $table) {
            $table->dropColumn('short_symbol');
        });
    }
}

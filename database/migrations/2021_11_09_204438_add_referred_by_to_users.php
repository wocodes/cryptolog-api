<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferredByToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('referred_by')->after('fiat_id')->nullable()->constrained('users');
        });


        Schema::table('waitlists', function (Blueprint $table) {
            $table->foreignId('referred_by')->after('invited')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referred_by');
        });

        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropColumn('referred_by');
        });
    }
}

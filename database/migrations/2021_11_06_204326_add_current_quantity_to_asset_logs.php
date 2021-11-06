<?php

use App\Models\AssetLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentQuantityToAssetLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->decimal('current_quantity', 16, 8)->after('quantity_bought')->default(0.0);
        });

        $logs = AssetLog::all();

        foreach ($logs as $log) {
            $log->current_quantity = $log->quantity_bought;
            $log->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->dropColumn('current_quantity');
        });
    }
}

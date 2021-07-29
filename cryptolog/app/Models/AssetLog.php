<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    protected $fillable = ["platform_id", "asset_id", "quantity_bought", "initial_value", "current_value", "profit_loss", "24_hr_change", "date_bought", "roi", "daily_roi", "current_price", "last_updated_at", "profit_loss_naira"];


    protected $with = ["asset"];


    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}

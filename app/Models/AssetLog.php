<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    protected $fillable = [
        "platform_id", "asset_id", "quantity_bought", "initial_value", "initial_value_fiat",
        "current_value", "current_value_fiat", "profit_loss", "24_hr_change", "date_bought",
        "roi", "daily_roi", "current_price", "last_updated_at", "profit_loss_fiat", "is_sold",
        "current_quantity", "asset_location_id"
    ];


    protected $with = ["asset:id,name,symbol"];


    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

//    protected function serializeDate(DateTimeInterface $date)
//    {
//        return $date->format('Y-m-d H:i:s');
//    }
}

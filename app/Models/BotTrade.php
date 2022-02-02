<?php

namespace App\Models;

use App\BotTradeLog;
use Illuminate\Database\Eloquent\Model;

class BotTrade extends Model
{
    protected $fillable = ['user_id', 'asset_id', 'is_active', 'initial_value', 'current_value'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function log()
    {
        return $this->hasOne(BotTradeLog::class);
    }
}

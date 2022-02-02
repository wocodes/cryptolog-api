<?php

namespace App;

use App\Models\BotTrade;
use Illuminate\Database\Eloquent\Model;

class BotTradeLog extends Model
{

    protected $fillable = ['bot_trade_id', 'value_bought', 'qty_bought', 'value_sold', 'qty_sold'];

    public function botTrade()
    {
        return $this->belongsTo(BotTrade::class);
    }
}

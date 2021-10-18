<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fiat extends Model
{
    protected $fillable = [
        'name', 'country_code', 'symbol', 'usdt_sell_rate', 'usdt_buy_rate', 'others_sell_rate', 'others_buy_rate'
    ];

    protected $casts = [
        'others_sell_rate' => 'json',
        'others_buy_rate' => 'json'
    ];
}

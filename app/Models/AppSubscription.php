<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSubscription extends Model
{
    public const FREE_PERMISSIONS = ["log-asset", "calculator"];
    public const PAID_PERMISSIONS = ["co-own-asset", "bot-trade", "trading-tips"];

    protected $fillable = ["user_id", "is_active", "start_date", "end_date"];
}

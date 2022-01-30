<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{

    protected $fillable = ["current_balance", "user_id"];

    public function transaction()
    {
        return $this->morphMany(Transaction::class, 'item');
    }
}

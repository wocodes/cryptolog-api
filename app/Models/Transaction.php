<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ["value", "description", "transaction_reference", "status"];

    public function item()
    {
        return $this->morphTo();
    }
}

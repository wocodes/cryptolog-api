<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
      'initial_value', 'current_value', 'quantity', 'date'
    ];
}

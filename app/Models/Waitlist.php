<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Waitlist extends Model
{
    protected $fillable = ['email', 'invited'];
}

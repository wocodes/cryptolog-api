<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $guarded = ['id'];

    public function model() {
        return $this->morphTo();
    }
}

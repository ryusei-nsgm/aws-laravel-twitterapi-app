<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BazzReach extends Model
{
    use HasFactory;

    public function sentiment()
    {
        return $this->hasMany('App\Models\Sentiment');
    }

    public function keyPhrase()
    {
        return $this->hasMany('App\Models\KeyPhrase');
    }
}

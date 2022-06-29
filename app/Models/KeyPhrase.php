<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyPhrase extends Model
{
    use HasFactory;

    public function bazzReach()
    {
        return $this->belongsTo('App\Models\BazzReach');
    }
}

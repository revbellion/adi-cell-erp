<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InitialCapital extends Model
{
    protected $fillable = [
        'amount',
        'date',
        'description',
    ];
}

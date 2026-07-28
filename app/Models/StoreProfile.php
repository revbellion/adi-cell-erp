<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreProfile extends Model
{
    protected $fillable = [
        'store_name',
        'address',
        'phone',
        'email',
        'footer_text',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreHours extends Model
{
    //
    protected $fillable = [
        'date',
        'open_time',
        'close_time',
        'is_closed'
    ];
}

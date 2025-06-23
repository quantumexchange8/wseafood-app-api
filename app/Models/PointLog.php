<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'adjust_type',
        'amount',
        'earning_point',
        'old_point',
        'new_point',
        'remark',
    ];
}

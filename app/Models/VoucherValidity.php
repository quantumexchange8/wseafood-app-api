<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherValidity extends Model
{
    use SoftDeletes;

    protected $table = 'voucher_validities';

    protected $fillable = [
        'voucher_id',
        'type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'weekday',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherMemberRules extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'voucher_id',
        'activation_rule',
        'event_type',
        'special_holiday_date',
        'amount_paid',
    ];

    protected function casts(): array
    {
        return [
            'special_holiday_date' => 'date',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVoucherRedemption extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'claimed_method',
        'redeemed_at',
        'status',
        'meta',
        'used_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'redeemed_at' => 'datetime',
            'meta' => 'array',
            'expired_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'id');
    }
}

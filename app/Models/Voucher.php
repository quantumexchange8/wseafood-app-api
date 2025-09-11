<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Voucher extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $guarded = ['id'];

    // Relations
    public function member_rule(): HasOne
    {
        return $this->hasOne(VoucherMemberRules::class);
    }

    public function validities(): HasMany
    {
        return $this->hasMany(VoucherValidity::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(UserVoucherRedemption::class);
    }

    public function getVoucherMediaUrlAttribute(): ?string
    {
        if ($this->hasMedia('voucher_thumbnail')) {
            return $this->getFirstMediaUrl('voucher_thumbnail');
        } else {
            return null;
        }
    }
}

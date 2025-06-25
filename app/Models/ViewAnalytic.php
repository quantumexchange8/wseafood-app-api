<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ViewAnalytic extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'user_id',
        'type',
        'ip_address',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}

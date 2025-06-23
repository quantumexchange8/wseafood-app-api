<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierItemToGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'modifier_group_id',
        'modifier_item_id',
        'position',
        'status',
        'price',
        'default',
    ];

    public function modifierItem(): BelongsTo
    {
        return $this->belongsTo(ModifierItem::class, 'modifier_item_id', 'id');
    }

    public function modifierGroup(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id', 'id');
    }
}

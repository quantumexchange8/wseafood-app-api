<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group_name',
        'display_name',
        'group_type',
        'min_selection',
        'max_selection',
        'status',
    ];

    public function hasProductIds(): HasMany
    {
        return $this->hasMany(ProductToModifierGroup::class, 'modifier_group_id', 'id');
    }

    public function hasModifierItemIds(): HasMany
    {
        return $this->hasMany(ModifierItemToGroup::class, 'modifier_group_id', 'id');
    }
}

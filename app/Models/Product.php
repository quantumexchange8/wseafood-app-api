<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes, HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'product_code',
        'name',
        'category_id',
        'price',
        'status',
        'reward_point',
        'set_meal',
    ];

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function getProductMediaUrlAttribute(): ?string
    {
        if ($this->hasMedia('product_photo')) {
            return $this->getFirstMediaUrl('product_photo');
        } else {
            return null;
        }
    }
}

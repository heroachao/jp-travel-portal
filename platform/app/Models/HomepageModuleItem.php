<?php

namespace App\Models;

use Database\Factories\HomepageModuleItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HomepageModuleItem extends Model
{
    /** @use HasFactory<HomepageModuleItemFactory> */
    use HasFactory;

    protected $fillable = [
        'homepage_module_id',
        'item_type',
        'item_id',
        'label',
        'summary',
        'sort_order',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(HomepageModule::class, 'homepage_module_id');
    }

    public function homepageModule(): BelongsTo
    {
        return $this->belongsTo(HomepageModule::class, 'homepage_module_id');
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}

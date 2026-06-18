<?php

namespace App\Models;

use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends Model
{
    /** @use HasFactory<DestinationFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'slug',
        'excerpt',
        'body',
        'latitude',
        'longitude',
        'cover_media_id',
        'seo_title',
        'meta_description',
        'is_indexable',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_indexable' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)->withTimestamps();
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class)->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

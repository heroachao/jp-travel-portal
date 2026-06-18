<?php

namespace App\Models;

use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    /** @use HasFactory<TopicFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_media_id',
        'seo_title',
        'meta_description',
        'is_indexable',
    ];

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
        ];
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)->withTimestamps();
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class)->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

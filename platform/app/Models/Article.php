<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'cover_media_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'source_name',
        'source_url',
        'status',
        'published_at',
        'display_updated_at',
        'reading_time_minutes',
        'popularity_score',
        'has_coupon',
        'scheduled_for',
        'rejection_reason',
        'seo_title',
        'meta_description',
        'canonical_url',
        'og_title',
        'og_description',
        'og_media_id',
        'is_indexable',
        'structured_data_type',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'display_updated_at' => 'datetime',
            'reading_time_minutes' => 'integer',
            'popularity_score' => 'integer',
            'has_coupon' => 'boolean',
            'scheduled_for' => 'datetime',
            'is_indexable' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'og_media_id');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class)->withTimestamps();
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class)->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function travelCategories(): BelongsToMany
    {
        return $this->belongsToMany(TravelCategory::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('travel_categories.id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ArticleFaq::class)->ordered();
    }

    public function enabledFaqs(): HasMany
    {
        return $this->hasMany(ArticleFaq::class)->enabled()->ordered();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArticleVersion::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ArticleStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

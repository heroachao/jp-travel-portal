<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Support\PublicUrl;
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

    public function firstImageUrl(int $width = 960): ?string
    {
        if (! $this->body) {
            return null;
        }

        if (! preg_match('/<img[^>]+src=(["\'])(.*?)\1/i', $this->body, $matches)) {
            return null;
        }

        return self::optimizedImageUrl(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5), $width);
    }

    public function firstImageAlt(): string
    {
        if (! $this->body) {
            return $this->title;
        }

        if (preg_match('/<img[^>]+alt=(["\'])(.*?)\1/i', $this->body, $matches)) {
            $alt = trim(strip_tags(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5)));

            if ($alt !== '') {
                return $alt;
            }
        }

        return $this->title;
    }

    public function optimizedBodyHtml(): string
    {
        if (! $this->body) {
            return '';
        }

        $html = preg_replace_callback('/<img\b[^>]*\bsrc=(["\'])(.*?)\1[^>]*>/i', function (array $matches): string {
            $imageTag = $matches[0];
            $src = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
            $optimizedSrc = e(self::optimizedImageUrl($src));
            $imageTag = preg_replace_callback(
                '/\bsrc=(["\']).*?\1/i',
                fn (): string => 'src="'.$optimizedSrc.'"',
                $imageTag,
            ) ?: $imageTag;

            if (! preg_match('/\bloading=/i', $imageTag)) {
                $imageTag = preg_replace('/<img\b/i', '<img loading="lazy"', $imageTag, 1) ?: $imageTag;
            }

            if (! preg_match('/\bdecoding=/i', $imageTag)) {
                $imageTag = preg_replace('/<img\b/i', '<img decoding="async"', $imageTag, 1) ?: $imageTag;
            }

            return $imageTag;
        }, $this->body) ?: $this->body;

        return preg_replace_callback('/\bhref=(["\'])(.*?)\1/i', function (array $matches): string {
            $href = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
            $canonicalHref = self::canonicalInternalHref($href);

            if ($canonicalHref === null) {
                return $matches[0];
            }

            return 'href="'.e($canonicalHref).'"';
        }, $html) ?: $html;
    }

    public static function optimizedImageUrl(string $url, int $width = 960): string
    {
        return $url;
    }

    private static function canonicalInternalHref(string $href): ?string
    {
        if ($href === '' || str_starts_with($href, '#')) {
            return null;
        }

        $siteBase = rtrim((string) config('app.url', 'https://japantriptools.com'), '/');
        $siteHost = (string) parse_url($siteBase, PHP_URL_HOST);
        $siteScheme = (string) (parse_url($siteBase, PHP_URL_SCHEME) ?: 'https');
        $parsed = parse_url($href);

        if ($parsed === false) {
            return null;
        }

        if (isset($parsed['host']) && ! in_array($parsed['host'], array_filter([$siteHost, 'japantriptools.com']), true)) {
            return null;
        }

        if (isset($parsed['scheme']) && ! in_array($parsed['scheme'], ['http', 'https'], true)) {
            return null;
        }

        $path = $parsed['path'] ?? '';

        if ($path === '' || ! str_starts_with($path, '/')) {
            return null;
        }

        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

        return PublicUrl::canonicalize($siteScheme.'://'.$siteHost.$path.$query.$fragment);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

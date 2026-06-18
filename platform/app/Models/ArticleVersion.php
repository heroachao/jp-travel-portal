<?php

namespace App\Models;

use Database\Factories\ArticleVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleVersion extends Model
{
    /** @use HasFactory<ArticleVersionFactory> */
    use HasFactory;

    protected $fillable = ['article_id', 'user_id', 'event', 'snapshot'];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

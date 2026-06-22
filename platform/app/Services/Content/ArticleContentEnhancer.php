<?php

namespace App\Services\Content;

use App\Models\Article;

class ArticleContentEnhancer
{
    /**
     * @return array{html: string, word_count: int, reading_time_minutes: int, reviewed_at: string|null, source_label: string|null}
     */
    public function build(Article $article): array
    {
        $wordCount = str_word_count(strip_tags((string) $article->body));

        return [
            'html' => '',
            'word_count' => $wordCount,
            'reading_time_minutes' => max(1, (int) ceil($wordCount / 220)),
            'reviewed_at' => ($article->display_updated_at ?: $article->updated_at)?->format('F j, Y'),
            'source_label' => $article->source_name,
        ];
    }
}

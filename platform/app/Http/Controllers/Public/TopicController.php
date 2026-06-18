<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class TopicController extends Controller
{
    public function show(Topic $topic): View
    {
        abort_unless($topic->is_indexable, 404);

        $topic->load(['articles' => fn ($query) => $query->published()->latest('published_at'), 'destinations']);

        return view('public.topics.show', [
            'meta' => new MetaPayload(
                $topic->seo_title ?: $topic->title,
                $topic->meta_description,
                route('topics.show', $topic),
            ),
            'topic' => $topic,
        ]);
    }
}

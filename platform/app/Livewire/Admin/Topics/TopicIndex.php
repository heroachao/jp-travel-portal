<?php

namespace App\Livewire\Admin\Topics;

use App\Models\Topic;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TopicIndex extends Component
{
    public ?int $topicId = null;

    public string $title = '';

    public string $slug = '';

    public ?string $excerpt = null;

    public function edit(int $id): void
    {
        $topic = Topic::findOrFail($id);
        $this->topicId = $topic->id;
        $this->title = $topic->title;
        $this->slug = $topic->slug;
        $this->excerpt = $topic->excerpt;
    }

    public function save(): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:140'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:160', Rule::unique('topics', 'slug')->ignore($this->topicId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
        ]);

        Topic::updateOrCreate(['id' => $this->topicId], $data);
        $this->reset(['topicId', 'title', 'slug', 'excerpt']);
    }

    public function delete(int $id): void
    {
        Topic::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.topics.topic-index', [
            'topics' => Topic::query()->latest()->get(),
        ])->layout('layouts.admin', ['title' => '专题管理']);
    }
}

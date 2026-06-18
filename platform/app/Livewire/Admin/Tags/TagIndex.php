<?php

namespace App\Livewire\Admin\Tags;

use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TagIndex extends Component
{
    public ?int $tagId = null;

    public string $name = '';

    public string $slug = '';

    public ?string $description = null;

    public function edit(int $id): void
    {
        $tag = Tag::findOrFail($id);
        $this->tagId = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->description = $tag->description;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('tags', 'slug')->ignore($this->tagId)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Tag::updateOrCreate(['id' => $this->tagId], $data);
        $this->reset(['tagId', 'name', 'slug', 'description']);
    }

    public function delete(int $id): void
    {
        Tag::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.tags.tag-index', [
            'tags' => Tag::query()->latest()->get(),
        ])->layout('layouts.admin', ['title' => '标签管理']);
    }
}

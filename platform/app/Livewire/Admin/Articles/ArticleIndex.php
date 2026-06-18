<?php

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $articles = Article::query()
            ->with('author')
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.articles.article-index', [
            'articles' => $articles,
            'statuses' => ArticleStatus::cases(),
        ])->layout('layouts.admin', ['title' => '文章管理']);
    }
}

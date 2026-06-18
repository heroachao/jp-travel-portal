<?php

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class ArticleForm extends Component
{
    public ?int $articleId = null;

    public string $title = '';

    public string $slug = '';

    public ?string $excerpt = null;

    public ?string $body = null;

    public ?string $seo_title = null;

    public ?string $meta_description = null;

    public ?string $canonical_url = null;

    public bool $is_indexable = true;

    public function mount(?Article $article = null): void
    {
        if (! $article?->exists) {
            return;
        }

        $this->articleId = $article->id;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->excerpt = $article->excerpt;
        $this->body = $article->body;
        $this->seo_title = $article->seo_title;
        $this->meta_description = $article->meta_description;
        $this->canonical_url = $article->canonical_url;
        $this->is_indexable = $article->is_indexable;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:180', Rule::unique('articles', 'slug')->ignore($this->articleId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:260'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'is_indexable' => ['boolean'],
        ];
    }

    public function save(): RedirectResponse|Redirector
    {
        $data = $this->validate();
        $existing = $this->articleId ? Article::findOrFail($this->articleId) : null;
        $data['author_id'] = $existing?->author_id ?? auth()->id();
        $data['status'] = $existing?->status ?? ArticleStatus::Draft;

        $article = Article::updateOrCreate(['id' => $this->articleId], $data);

        session()->flash('status', '文章已保存');

        return redirect()->route('admin.articles.edit', $article);
    }

    public function render(): View
    {
        return view('livewire.admin.articles.article-form')
            ->layout('layouts.admin', ['title' => $this->articleId ? '编辑文章' : '新建文章']);
    }
}

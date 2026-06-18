<?php

namespace App\Livewire\Admin\Articles;

use App\Models\Article;
use App\Services\Publishing\ArticleWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class ReviewPanel extends Component
{
    public Article $article;

    public string $rejectionReason = '';

    public ?string $scheduledFor = null;

    public function mount(Article $article): void
    {
        $this->article = $article;
    }

    public function submitForReview(ArticleWorkflow $workflow): RedirectResponse|Redirector
    {
        $workflow->submitForReview($this->article, auth()->user());

        session()->flash('status', '文章已提交审核');

        return redirect()->route('admin.articles.review', $this->article);
    }

    public function reject(ArticleWorkflow $workflow): RedirectResponse|Redirector
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'max:1000'],
        ]);

        $workflow->reject($this->article, auth()->user(), $this->rejectionReason);

        session()->flash('status', '文章已退回修改');

        return redirect()->route('admin.articles.review', $this->article);
    }

    public function publish(ArticleWorkflow $workflow): RedirectResponse|Redirector
    {
        $workflow->publish($this->article, auth()->user());

        session()->flash('status', '文章已发布');

        return redirect()->route('admin.articles.review', $this->article);
    }

    public function schedule(ArticleWorkflow $workflow): RedirectResponse|Redirector
    {
        $this->validate([
            'scheduledFor' => ['required', 'date', 'after:now'],
        ]);

        $workflow->schedule($this->article, auth()->user(), Carbon::parse($this->scheduledFor));

        session()->flash('status', '文章已设置定时发布');

        return redirect()->route('admin.articles.review', $this->article);
    }

    public function render(): View
    {
        $this->article->refresh();

        return view('livewire.admin.articles.review-panel')
            ->layout('layouts.admin', ['title' => '审核文章']);
    }
}

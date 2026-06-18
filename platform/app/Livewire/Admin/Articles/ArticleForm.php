<?php

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\TravelCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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

    public ?string $source_name = null;

    public ?string $source_url = null;

    public ?string $display_updated_at = null;

    public ?int $reading_time_minutes = null;

    public int $popularity_score = 0;

    public bool $has_coupon = false;

    public ?string $seo_title = null;

    public ?string $meta_description = null;

    public ?string $canonical_url = null;

    public bool $is_indexable = true;

    public array $selectedCategoryIds = [];

    public array $faqs = [];

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
        $this->source_name = $article->source_name;
        $this->source_url = $article->source_url;
        $this->display_updated_at = $article->display_updated_at?->format('Y-m-d\TH:i');
        $this->reading_time_minutes = $article->reading_time_minutes;
        $this->popularity_score = $article->popularity_score ?? 0;
        $this->has_coupon = (bool) ($article->has_coupon ?? false);
        $this->seo_title = $article->seo_title;
        $this->meta_description = $article->meta_description;
        $this->canonical_url = $article->canonical_url;
        $this->is_indexable = $article->is_indexable;
        $this->selectedCategoryIds = $article->travelCategories()
            ->pluck('travel_categories.id')
            ->map(fn (int $id): int => $id)
            ->all();
        $this->faqs = $article->faqs()
            ->get()
            ->map(fn ($faq): array => [
                'question' => $faq->question,
                'answer' => $faq->answer,
                'sort_order' => $faq->sort_order,
                'is_enabled' => $faq->is_enabled,
            ])
            ->all();
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:180', Rule::unique('articles', 'slug')->ignore($this->articleId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url:http,https', 'max:255'],
            'display_updated_at' => ['nullable', 'date'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'popularity_score' => ['integer', 'min:0', 'max:4294967295'],
            'has_coupon' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:260'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'is_indexable' => ['boolean'],
            'selectedCategoryIds' => ['array'],
            'selectedCategoryIds.*' => ['integer', Rule::exists('travel_categories', 'id')->whereNull('deleted_at')],
            'faqs' => ['array', 'max:20'],
            'faqs.*' => ['array'],
            'faqs.*.question' => ['nullable', 'string', 'max:255'],
            'faqs.*.answer' => ['nullable', 'string'],
            'faqs.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'faqs.*.is_enabled' => ['boolean'],
        ];
    }

    public function save(): RedirectResponse|Redirector
    {
        if ($this->display_updated_at === '') {
            $this->display_updated_at = null;
        }

        $this->withValidator(function ($validator): void {
            $validator->after(function ($validator): void {
                foreach ($this->faqs as $index => $faq) {
                    if (! is_array($faq)) {
                        continue;
                    }

                    $question = trim((string) ($faq['question'] ?? ''));
                    $answer = trim((string) ($faq['answer'] ?? ''));

                    if ($question === '' && $answer === '') {
                        continue;
                    }

                    if ($question === '') {
                        $validator->errors()->add("faqs.$index.question", 'FAQ 问题不能为空');
                    }

                    if ($answer === '') {
                        $validator->errors()->add("faqs.$index.answer", 'FAQ 答案不能为空');
                    }
                }
            });
        });

        $data = $this->validate();
        $categoryIds = collect($data['selectedCategoryIds'] ?? [])
            ->map(fn (int|string $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
        $faqRows = $this->normalizeFaqRows($data['faqs'] ?? []);

        unset($data['selectedCategoryIds'], $data['faqs']);

        if (($data['display_updated_at'] ?? null) === '') {
            $data['display_updated_at'] = null;
        }

        $existing = $this->articleId ? Article::findOrFail($this->articleId) : null;
        $data['author_id'] = $existing?->author_id ?? auth()->id();
        $data['status'] = $existing?->status ?? ArticleStatus::Draft;

        $article = DB::transaction(function () use ($data, $categoryIds, $faqRows): Article {
            $article = Article::updateOrCreate(['id' => $this->articleId], $data);
            $article->travelCategories()->sync($categoryIds);
            $article->faqs()->delete();

            foreach ($faqRows as $faqRow) {
                $article->faqs()->create($faqRow);
            }

            return $article;
        });

        session()->flash('status', '文章已保存');

        return redirect()->route('admin.articles.edit', $article);
    }

    public function addFaq(): void
    {
        $this->faqs[] = [
            'question' => '',
            'answer' => '',
            'sort_order' => count($this->faqs) + 1,
            'is_enabled' => true,
        ];
    }

    public function removeFaq(int $index): void
    {
        unset($this->faqs[$index]);

        $this->faqs = array_values($this->faqs);
    }

    public function render(): View
    {
        return view('livewire.admin.articles.article-form', [
            'categoryOptions' => TravelCategory::query()->ordered()->get(),
        ])
            ->layout('layouts.admin', ['title' => $this->articleId ? '编辑文章' : '新建文章']);
    }

    private function normalizeFaqRows(array $faqs): array
    {
        return collect($faqs)
            ->map(function (mixed $faq, int $index): ?array {
                if (! is_array($faq)) {
                    return null;
                }

                $question = trim((string) ($faq['question'] ?? ''));
                $answer = (string) ($faq['answer'] ?? '');

                if ($question === '' && trim($answer) === '') {
                    return null;
                }

                return [
                    'question' => $question,
                    'answer' => clean($answer),
                    'sort_order' => (int) ($faq['sort_order'] ?? $index + 1),
                    'is_enabled' => (bool) ($faq['is_enabled'] ?? false),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}

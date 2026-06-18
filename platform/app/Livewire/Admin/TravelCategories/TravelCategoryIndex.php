<?php

namespace App\Livewire\Admin\TravelCategories;

use App\Models\TravelCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TravelCategoryIndex extends Component
{
    public ?int $categoryId = null;

    public ?int $parent_id = null;

    public string $title = '';

    public ?string $display_name = null;

    public string $slug = '';

    public ?string $excerpt = null;

    public ?string $body = null;

    public ?string $seo_title = null;

    public ?string $meta_description = null;

    public bool $is_indexable = true;

    public bool $is_visible = true;

    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $category = TravelCategory::findOrFail($id);
        $this->categoryId = $category->id;
        $this->parent_id = $category->parent_id;
        $this->title = $category->title;
        $this->display_name = $category->display_name;
        $this->slug = $category->slug;
        $this->excerpt = $category->excerpt;
        $this->body = $category->body;
        $this->seo_title = $category->seo_title;
        $this->meta_description = $category->meta_description;
        $this->is_indexable = $category->is_indexable;
        $this->is_visible = $category->is_visible;
        $this->sort_order = $category->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('travel_categories', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:140'],
            'display_name' => ['nullable', 'string', 'max:140'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:160', Rule::unique('travel_categories', 'slug')->ignore($this->categoryId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:260'],
            'is_indexable' => ['boolean'],
            'is_visible' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ]);

        if ($this->parentCreatesLoop($data['parent_id'])) {
            $this->addError('parent_id', '父级频道不能选择自己或子级频道');

            return;
        }

        if ($data['body'] !== null) {
            $data['body'] = clean($data['body']);
        }

        TravelCategory::updateOrCreate(['id' => $this->categoryId], $data);
        session()->flash('status', '分类频道已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $category = TravelCategory::withCount(['articles', 'children'])->findOrFail($id);

        if ($category->children_count > 0 || $category->articles_count > 0) {
            $this->addError('delete', '分类频道已有子级或文章，不能删除');

            return;
        }

        $this->resetErrorBag('delete');
        $category->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.travel-categories.travel-category-index', [
            'categories' => TravelCategory::query()->with('parent')->ordered()->get(),
            'parentOptions' => $this->parentOptions(),
        ])->layout('layouts.admin', ['title' => '分类频道管理']);
    }

    private function resetForm(): void
    {
        $this->categoryId = null;
        $this->parent_id = null;
        $this->title = '';
        $this->display_name = null;
        $this->slug = '';
        $this->excerpt = null;
        $this->body = null;
        $this->seo_title = null;
        $this->meta_description = null;
        $this->is_indexable = true;
        $this->is_visible = true;
        $this->sort_order = 0;
    }

    private function parentCreatesLoop(?int $parentId): bool
    {
        if ($this->categoryId === null || $parentId === null) {
            return false;
        }

        $parentId = (int) $parentId;

        return $parentId === $this->categoryId
            || in_array($parentId, $this->descendantIds($this->categoryId), true);
    }

    private function parentOptions()
    {
        $query = TravelCategory::query()->ordered();

        if ($this->categoryId !== null) {
            $query->whereNotIn('id', array_merge([$this->categoryId], $this->descendantIds($this->categoryId)));
        }

        return $query->get();
    }

    /**
     * @return array<int>
     */
    private function descendantIds(int $categoryId): array
    {
        $descendantIds = [];
        $frontier = [$categoryId];

        while ($frontier !== []) {
            $childIds = TravelCategory::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $frontier = [];

            foreach ($childIds as $childId) {
                if (in_array($childId, $descendantIds, true)) {
                    continue;
                }

                $descendantIds[] = $childId;
                $frontier[] = $childId;
            }
        }

        return $descendantIds;
    }
}

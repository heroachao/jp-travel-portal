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

        if ($data['parent_id'] === $this->categoryId) {
            $data['parent_id'] = null;
        }

        TravelCategory::updateOrCreate(['id' => $this->categoryId], $data);
        session()->flash('status', '分类频道已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        TravelCategory::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.travel-categories.travel-category-index', [
            'categories' => TravelCategory::query()->with('parent')->ordered()->get(),
            'parentOptions' => TravelCategory::query()->ordered()->get(),
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
}

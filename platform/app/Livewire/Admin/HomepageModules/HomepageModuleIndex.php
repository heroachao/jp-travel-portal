<?php

namespace App\Livewire\Admin\HomepageModules;

use App\Models\HomepageModule;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class HomepageModuleIndex extends Component
{
    public ?int $moduleId = null;

    public string $placement_key = '';

    public string $type = 'featured_articles';

    public string $title = '';

    public ?string $subtitle = null;

    public bool $is_enabled = true;

    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $module = HomepageModule::findOrFail($id);
        $this->moduleId = $module->id;
        $this->placement_key = $module->placement_key;
        $this->type = $module->type;
        $this->title = $module->title;
        $this->subtitle = $module->subtitle;
        $this->is_enabled = $module->is_enabled;
        $this->sort_order = $module->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate([
            'placement_key' => ['required', 'alpha_dash:ascii', 'max:120', Rule::unique('homepage_modules', 'placement_key')->ignore($this->moduleId)],
            'type' => ['required', Rule::in(['featured_articles', 'latest_articles', 'popular_articles', 'region_grid', 'category_grid', 'service_highlights', 'travel_tools'])],
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'is_enabled' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ]);

        HomepageModule::updateOrCreate(['id' => $this->moduleId], $data);
        session()->flash('status', '首页模块已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        HomepageModule::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.homepage-modules.homepage-module-index', [
            'modules' => HomepageModule::query()->ordered()->get(),
        ])->layout('layouts.admin', ['title' => '首页模块管理']);
    }

    private function resetForm(): void
    {
        $this->moduleId = null;
        $this->placement_key = '';
        $this->type = 'featured_articles';
        $this->title = '';
        $this->subtitle = null;
        $this->is_enabled = true;
        $this->sort_order = 0;
    }
}

<?php

namespace App\Livewire\Admin\Ads;

use App\Models\AdPlacement;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AdPlacementIndex extends Component
{
    public ?int $placementId = null;

    public string $key = '';

    public string $name = '';

    public string $page_type = 'article';

    public string $position = 'body_middle';

    public ?string $code = null;

    public bool $is_enabled = false;

    public function edit(int $id): void
    {
        $placement = AdPlacement::findOrFail($id);
        $this->placementId = $placement->id;
        $this->key = $placement->key;
        $this->name = $placement->name;
        $this->page_type = $placement->page_type;
        $this->position = $placement->position;
        $this->code = $placement->code;
        $this->is_enabled = $placement->is_enabled;
    }

    public function save(): void
    {
        $data = $this->validate([
            'key' => ['required', 'alpha_dash:ascii', 'max:120', Rule::unique('ad_placements', 'key')->ignore($this->placementId)],
            'name' => ['required', 'string', 'max:120'],
            'page_type' => ['required', 'string', 'max:60'],
            'position' => ['required', 'string', 'max:80'],
            'code' => ['nullable', 'string'],
            'is_enabled' => ['boolean'],
        ]);

        AdPlacement::updateOrCreate(['id' => $this->placementId], $data);
        $this->reset(['placementId', 'key', 'name', 'code', 'is_enabled']);
        $this->page_type = 'article';
        $this->position = 'body_middle';
    }

    public function delete(int $id): void
    {
        AdPlacement::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.ads.ad-placement-index', [
            'placements' => AdPlacement::query()->latest()->get(),
        ])->layout('layouts.admin', ['title' => '广告管理']);
    }
}

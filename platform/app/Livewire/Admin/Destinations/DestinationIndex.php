<?php

namespace App\Livewire\Admin\Destinations;

use App\Models\Destination;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DestinationIndex extends Component
{
    public ?int $destinationId = null;

    public ?int $parent_id = null;

    public string $type = 'city';

    public string $name = '';

    public ?string $display_name = null;

    public string $slug = '';

    public ?string $excerpt = null;

    public ?string $body = null;

    public ?string $seo_title = null;

    public ?string $meta_description = null;

    public bool $is_indexable = true;

    public bool $is_channel = false;

    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $destination = Destination::findOrFail($id);
        $this->destinationId = $destination->id;
        $this->parent_id = $destination->parent_id;
        $this->type = $destination->type;
        $this->name = $destination->name;
        $this->display_name = $destination->display_name;
        $this->slug = $destination->slug;
        $this->excerpt = $destination->excerpt;
        $this->body = $destination->body;
        $this->seo_title = $destination->seo_title;
        $this->meta_description = $destination->meta_description;
        $this->is_indexable = $destination->is_indexable;
        $this->is_channel = $destination->is_channel;
        $this->sort_order = $destination->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('destinations', 'id')->whereNull('deleted_at')],
            'type' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:140', Rule::unique('destinations', 'slug')->ignore($this->destinationId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:260'],
            'is_indexable' => ['boolean'],
            'is_channel' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ]);

        if ($this->parentCreatesLoop($data['parent_id'])) {
            $this->addError('parent_id', '上级地区不能选择自己或下级地区');

            return;
        }

        Destination::updateOrCreate(['id' => $this->destinationId], $data);
        session()->flash('status', '目的地已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Destination::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.destinations.destination-index', [
            'destinations' => Destination::query()->with('parent')->ordered()->get(),
            'parentOptions' => $this->parentOptions(),
        ])->layout('layouts.admin', ['title' => '目的地管理']);
    }

    private function resetForm(): void
    {
        $this->destinationId = null;
        $this->parent_id = null;
        $this->type = 'city';
        $this->name = '';
        $this->display_name = null;
        $this->slug = '';
        $this->excerpt = null;
        $this->body = null;
        $this->seo_title = null;
        $this->meta_description = null;
        $this->is_indexable = true;
        $this->is_channel = false;
        $this->sort_order = 0;
    }

    private function parentCreatesLoop(?int $parentId): bool
    {
        if ($this->destinationId === null || $parentId === null) {
            return false;
        }

        $parentId = (int) $parentId;

        return $parentId === $this->destinationId
            || in_array($parentId, $this->descendantIds($this->destinationId), true);
    }

    private function parentOptions()
    {
        $query = Destination::query()->ordered();

        if ($this->destinationId !== null) {
            $query->whereNotIn('id', array_merge([$this->destinationId], $this->descendantIds($this->destinationId)));
        }

        return $query->get();
    }

    /**
     * @return array<int>
     */
    private function descendantIds(int $destinationId): array
    {
        $descendantIds = [];
        $frontier = [$destinationId];

        while ($frontier !== []) {
            $childIds = Destination::query()
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

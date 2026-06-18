<?php

namespace App\Livewire\Admin\Destinations;

use App\Models\Destination;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DestinationIndex extends Component
{
    public ?int $destinationId = null;

    public string $type = 'city';

    public string $name = '';

    public string $slug = '';

    public ?string $excerpt = null;

    public function edit(int $id): void
    {
        $destination = Destination::findOrFail($id);
        $this->destinationId = $destination->id;
        $this->type = $destination->type;
        $this->name = $destination->name;
        $this->slug = $destination->slug;
        $this->excerpt = $destination->excerpt;
    }

    public function save(): void
    {
        $data = $this->validate([
            'type' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:140', Rule::unique('destinations', 'slug')->ignore($this->destinationId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
        ]);

        Destination::updateOrCreate(['id' => $this->destinationId], $data);
        $this->reset(['destinationId', 'name', 'slug', 'excerpt']);
        $this->type = 'city';
    }

    public function delete(int $id): void
    {
        Destination::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.destinations.destination-index', [
            'destinations' => Destination::query()->latest()->get(),
        ])->layout('layouts.admin', ['title' => '目的地管理']);
    }
}

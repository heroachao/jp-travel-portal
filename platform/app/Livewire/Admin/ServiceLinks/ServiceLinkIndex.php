<?php

namespace App\Livewire\Admin\ServiceLinks;

use App\Models\ServiceLink;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ServiceLinkIndex extends Component
{
    public ?int $serviceLinkId = null;

    public string $type = 'guide';

    public string $label = '';

    public string $url = '';

    public string $placement = 'header';

    public ?string $tracking_key = null;

    public ?string $notes = null;

    public bool $is_enabled = true;

    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $serviceLink = ServiceLink::findOrFail($id);
        $this->serviceLinkId = $serviceLink->id;
        $this->type = $serviceLink->type;
        $this->label = $serviceLink->label;
        $this->url = $serviceLink->url;
        $this->placement = $serviceLink->placement;
        $this->tracking_key = $serviceLink->tracking_key;
        $this->notes = $serviceLink->notes;
        $this->is_enabled = $serviceLink->is_enabled;
        $this->sort_order = $serviceLink->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate([
            'type' => ['required', Rule::in(['guide', 'activity', 'hotel', 'flight', 'rail', 'shop', 'community', 'exchange_rate', 'advertising', 'custom'])],
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url:http,https', 'max:255'],
            'placement' => ['required', Rule::in(['header', 'footer'])],
            'tracking_key' => ['nullable', 'alpha_dash:ascii', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_enabled' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ]);

        ServiceLink::updateOrCreate(['id' => $this->serviceLinkId], $data);
        session()->flash('status', '服务入口已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        ServiceLink::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.service-links.service-link-index', [
            'serviceLinks' => ServiceLink::query()->ordered()->get(),
        ])->layout('layouts.admin', ['title' => '服务入口管理']);
    }

    private function resetForm(): void
    {
        $this->serviceLinkId = null;
        $this->type = 'guide';
        $this->label = '';
        $this->url = '';
        $this->placement = 'header';
        $this->tracking_key = null;
        $this->notes = null;
        $this->is_enabled = true;
        $this->sort_order = 0;
    }
}

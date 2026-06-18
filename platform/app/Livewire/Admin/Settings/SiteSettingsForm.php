<?php

namespace App\Livewire\Admin\Settings;

use App\Services\Settings\SiteSettings;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SiteSettingsForm extends Component
{
    public string $site_name = 'Japan Travel Guide';

    public string $seo_title_suffix = 'Japan Travel Guide';

    public ?string $default_meta_description = null;

    public ?string $ga4_measurement_id = null;

    public ?string $adsense_publisher_id = null;

    public bool $analytics_enabled = false;

    public bool $ads_enabled = false;

    public function mount(SiteSettings $settings): void
    {
        $current = $settings->current();

        $this->site_name = $current->site_name;
        $this->seo_title_suffix = $current->seo_title_suffix;
        $this->default_meta_description = $current->default_meta_description;
        $this->ga4_measurement_id = $current->ga4_measurement_id;
        $this->adsense_publisher_id = $current->adsense_publisher_id;
        $this->analytics_enabled = $current->analytics_enabled;
        $this->ads_enabled = $current->ads_enabled;
    }

    public function save(SiteSettings $settings): void
    {
        $data = $this->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'seo_title_suffix' => ['required', 'string', 'max:120'],
            'default_meta_description' => ['nullable', 'string', 'max:260'],
            'ga4_measurement_id' => ['nullable', 'regex:/^G-[A-Z0-9]{8,16}$/'],
            'adsense_publisher_id' => ['nullable', 'regex:/^ca-pub-[0-9]{16}$/'],
            'analytics_enabled' => ['boolean'],
            'ads_enabled' => ['boolean'],
        ]);

        $settings->update($data);
        session()->flash('status', '站点设置已保存');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site-settings-form')
            ->layout('layouts.admin', ['title' => '站点设置']);
    }
}

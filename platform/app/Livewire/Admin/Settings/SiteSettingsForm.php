<?php

namespace App\Livewire\Admin\Settings;

use App\Services\Settings\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SiteSettingsForm extends Component
{
    public string $site_name = 'Japan Travel Guide';

    public string $seo_title_suffix = 'Japan Travel Guide';

    public ?string $tagline = null;

    public ?string $default_meta_description = null;

    public ?string $ga4_measurement_id = null;

    public ?string $adsense_publisher_id = null;

    public bool $organization_schema_enabled = false;

    public ?string $contact_email = null;

    public ?string $social_links = null;

    public ?string $robots_extra_rules = null;

    public bool $analytics_enabled = false;

    public bool $ads_enabled = false;

    public function mount(SiteSettings $settings): void
    {
        $current = $settings->current();

        $this->site_name = $current->site_name;
        $this->seo_title_suffix = $current->seo_title_suffix;
        $this->tagline = $current->tagline;
        $this->default_meta_description = $current->default_meta_description;
        $this->ga4_measurement_id = $current->ga4_measurement_id;
        $this->adsense_publisher_id = $current->adsense_publisher_id;
        $this->organization_schema_enabled = $current->organization_schema_enabled;
        $this->contact_email = $current->contact_email;
        $this->social_links = $current->social_links === null
            ? null
            : json_encode($current->social_links, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->robots_extra_rules = $current->robots_extra_rules;
        $this->analytics_enabled = $current->analytics_enabled;
        $this->ads_enabled = $current->ads_enabled;
    }

    public function save(SiteSettings $settings): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);

        $this->normalizeNullableStrings();

        $data = $this->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'seo_title_suffix' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'default_meta_description' => ['nullable', 'string', 'max:255'],
            'ga4_measurement_id' => ['nullable', 'string', 'max:255', 'regex:/^G-[A-Z0-9]+$/'],
            'adsense_publisher_id' => ['nullable', 'string', 'max:255', 'regex:/^ca-pub-[0-9]+$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'social_links' => ['nullable', 'json'],
            'robots_extra_rules' => ['nullable', 'string', 'max:2000'],
            'analytics_enabled' => ['boolean'],
            'ads_enabled' => ['boolean'],
            'organization_schema_enabled' => ['boolean'],
        ]);

        $data['social_links'] = $this->decodeSocialLinks($data['social_links'] ?? null);

        $settings->update($data);
        session()->flash('status', '站点设置已保存');
    }

    private function normalizeNullableStrings(): void
    {
        foreach ([
            'tagline',
            'default_meta_description',
            'ga4_measurement_id',
            'adsense_publisher_id',
            'contact_email',
            'social_links',
            'robots_extra_rules',
        ] as $field) {
            $this->{$field} = $this->nullableText($this->{$field});
        }
    }

    private function nullableText(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $value;
    }

    private function decodeSocialLinks(?string $json): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'social_links' => '社交链接必须是 JSON 对象或数组。',
            ]);
        }

        return $decoded;
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site-settings-form')
            ->layout('layouts.admin', ['title' => '站点设置']);
    }
}

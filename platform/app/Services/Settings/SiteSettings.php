<?php

namespace App\Services\Settings;

use App\Models\SiteSetting;

class SiteSettings
{
    public function current(): SiteSetting
    {
        return SiteSetting::query()->find(1) ?? new SiteSetting([
            'site_name' => 'Japan Travel Guide',
            'seo_title_suffix' => 'Japan Travel Guide',
            'tagline' => null,
            'default_meta_description' => 'Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.',
            'organization_schema_enabled' => false,
            'contact_email' => null,
            'social_links' => null,
            'robots_extra_rules' => null,
            'analytics_enabled' => false,
            'ads_enabled' => false,
        ]);
    }

    public function update(array $data): SiteSetting
    {
        return SiteSetting::query()->updateOrCreate(['id' => 1], $data);
    }
}

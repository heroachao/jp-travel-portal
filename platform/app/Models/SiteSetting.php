<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'seo_title_suffix',
        'tagline',
        'default_meta_description',
        'ga4_measurement_id',
        'adsense_publisher_id',
        'organization_schema_enabled',
        'contact_email',
        'social_links',
        'robots_extra_rules',
        'analytics_enabled',
        'ads_enabled',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'organization_schema_enabled' => 'boolean',
            'analytics_enabled' => 'boolean',
            'ads_enabled' => 'boolean',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'seo_title_suffix',
        'default_meta_description',
        'ga4_measurement_id',
        'adsense_publisher_id',
        'analytics_enabled',
        'ads_enabled',
    ];

    protected function casts(): array
    {
        return [
            'analytics_enabled' => 'boolean',
            'ads_enabled' => 'boolean',
        ];
    }
}

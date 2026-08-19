<?php

namespace App\Services\Ads;

use App\Models\AdPlacement;
use App\Services\Settings\SiteSettings;
use Illuminate\Support\HtmlString;

class AdRenderer
{
    public function __construct(private readonly SiteSettings $siteSettings)
    {
    }

    public function render(string $key): HtmlString
    {
        $settings = $this->siteSettings->current();

        if (! $settings->ads_enabled) {
            return new HtmlString('');
        }

        $placement = AdPlacement::query()
            ->where('key', $key)
            ->where('is_enabled', true)
            ->first();

        if (! $placement || blank($placement->code)) {
            return new HtmlString('');
        }

        return new HtmlString('<div class="ad-slot" data-ad-key="'.e($key).'">'.$placement->code.'</div>');
    }
}

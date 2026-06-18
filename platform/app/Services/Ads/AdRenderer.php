<?php

namespace App\Services\Ads;

use App\Models\AdPlacement;
use Illuminate\Support\HtmlString;

class AdRenderer
{
    public function render(string $key): HtmlString
    {
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

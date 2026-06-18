<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Settings\SiteSettings;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(SiteSettings $settings): Response
    {
        $rules = [
            'User-agent: *',
            'Disallow:',
            'Sitemap: '.route('sitemap'),
        ];

        $extraRules = trim((string) $settings->current()->robots_extra_rules);

        if ($extraRules !== '') {
            $rules[] = '';
            $rules[] = $extraRules;
        }

        return response(implode("\n", $rules)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}

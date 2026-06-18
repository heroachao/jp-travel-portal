<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SitemapBuilder $builder): Response
    {
        return response($builder->build(), 200, ['Content-Type' => 'application/xml']);
    }
}

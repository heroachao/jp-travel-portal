<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        return response("User-agent: *\nDisallow:\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']);
    }
}

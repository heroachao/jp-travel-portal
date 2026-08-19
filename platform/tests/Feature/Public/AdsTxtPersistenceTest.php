<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class AdsTxtPersistenceTest extends TestCase
{
    private const CONTENT = "google.com, pub-3754179629894278, DIRECT, f08c47fec0942fa0\n";

    public function test_ads_txt_source_file_is_present_and_exact(): void
    {
        $path = public_path('ads.txt');

        $this->assertFileExists($path);
        $this->assertSame(self::CONTENT, file_get_contents($path));
    }

    public function test_ads_txt_route_returns_the_exact_source_file(): void
    {
        $this->get('/ads.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertContent(self::CONTENT);
    }
}

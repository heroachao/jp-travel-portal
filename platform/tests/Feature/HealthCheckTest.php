<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_page_returns_ok(): void
    {
        $this->get('/health')
            ->assertOk()
            ->assertSee('OK');
    }
}

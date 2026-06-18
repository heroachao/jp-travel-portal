<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authenticated_authorized_user(): void
    {
        $this->get('/admin/articles')->assertRedirect('/login');

        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/articles')->assertForbidden();
    }
}

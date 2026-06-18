<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logger_writes_admin_action(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/admin/articles', 'POST');
        $request->setUserResolver(fn () => $user);

        app(AuditLogger::class)->log($request, 'admin.request', null, ['status' => 200]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'admin.request',
        ]);
    }
}

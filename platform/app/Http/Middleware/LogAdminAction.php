<?php

namespace App\Http\Middleware;

use App\Services\Audit\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminAction
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && ! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            app(AuditLogger::class)->log($request, 'admin.request', null, [
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }
}

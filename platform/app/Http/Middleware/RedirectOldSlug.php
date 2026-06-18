<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectOldSlug
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $redirect = Redirect::query()
                ->where('from_path', '/'.$request->path())
                ->first();
        } catch (QueryException) {
            $redirect = null;
        }

        if ($redirect) {
            return redirect($redirect->to_path, $redirect->status_code);
        }

        return $next($request);
    }
}

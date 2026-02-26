<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->is_admin === false) {
            abort(403, 'غير مصرح لك بالوصول.');
        }

        return $next($request);
    }
}

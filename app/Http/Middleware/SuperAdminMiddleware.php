<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        $superAdminEmail = config('app.super_admin_email');

        if (auth()->check() && auth()->user()->email === $superAdminEmail) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}

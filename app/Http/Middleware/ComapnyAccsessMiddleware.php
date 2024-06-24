<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComapnyAccsessMiddleware
{
    public function handle($request, Closure $next)
    {
        $superAdminEmail = config('app.super_admin_email');
        if (auth()->check()) {
            if (
                auth()->user()->email == $superAdminEmail ||
                auth()->user()->role == 'company'
            ) {
                return $next($request);
            }

            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}

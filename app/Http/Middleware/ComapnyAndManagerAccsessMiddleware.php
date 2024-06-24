<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComapnyAndManagerAccsessMiddleware
{
    public function handle($request, Closure $next)
    {
        $superAdminEmail = config('app.super_admin_email');
        if (auth()->check()) {
            if (
                auth()->user()->email == $superAdminEmail ||
                auth()->user()->role == 'company' ||
                auth()->user()->role == 'manager'
            ) {
                return $next($request);
            }

            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}

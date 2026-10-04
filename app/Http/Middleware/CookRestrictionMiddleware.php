<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CookRestrictionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only restrict cooks (role_id 3). Everyone else passes through.
        if (!$user || $user->role_id !== 3) {
            return $next($request);
        }

        $allowed = $request->is(
            'kitchen*',
            'inventory*',
            'orders/*/complete',
            'profile',
            'password',
            'logout'
        );

        if (!$allowed) {
            return redirect('/kitchen');
        }

        return $next($request);
    }
}
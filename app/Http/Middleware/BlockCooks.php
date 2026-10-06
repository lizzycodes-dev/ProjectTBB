<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockCooks
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role_id === 3) {
            return redirect('/kitchen');
        }

        return $next($request);
    }
}
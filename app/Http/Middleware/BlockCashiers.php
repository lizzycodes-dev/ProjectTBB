<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockCashiers
{
    /**
     * Cashiers (role_id 2) only get the POS and the view-only kitchen.
     * Anything else sends them back to the POS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role_id === 2) {
            return redirect('/pos');
        }

        return $next($request);
    }
}
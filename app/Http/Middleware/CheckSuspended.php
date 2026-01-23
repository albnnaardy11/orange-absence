<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Always allow Login, Logout, and Portal routes to prevent lockouts/redirect loops
        if ($request->is('admin/login*') || 
            $request->is('member/login*') || 
            $request->is('*logout*') || 
            $request->is('/') || 
            $request->is('account-suspended')) {
            return $next($request);
        }

        // 2. If authenticated and suspended, block access to everything else
        if (auth()->check() && auth()->user()->is_suspended) {
            return response()->view('errors.account-suspended', [], 403);
        }

        return $next($request);
    }
}

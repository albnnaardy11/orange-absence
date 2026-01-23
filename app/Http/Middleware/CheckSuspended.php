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
        if (auth()->check() && auth()->user()->is_suspended) {
            // Ignore for the portal/root path
            if ($request->is('/')) {
                return $next($request);
            }

            // Allow logout requests to proceed
            if ($request->is('*logout*') || $request->isMethod('POST')) {
                // We actually want to allow POST logout specifically.
                // But better check the path precisely if possible.
            }
            
            // Refined: Allow if it's a logout path
            if (str_contains($request->path(), 'logout')) {
                return $next($request);
            }

            // Only redirect if not already on the suspended page
            if (!$request->is('account-suspended')) {
                return response()->view('errors.account-suspended', [], 403);
            }
        }

        return $next($request);
    }
}

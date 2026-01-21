<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->is_active) {
            // If the route is already the suspended page, allow it to prevent infinite loop
            if ($request->routeIs('account.suspended') || $request->is('account-suspended')) {
                return $next($request);
            }

            // Force logout or just redirect?
            // User requirement: "paksa logout atau redirect ke route /account-suspended"
            // If we just redirect, the session remains.
            // If we logout, they go to login page.
            // A common pattern: Redirect to a suspended page, and that page can strictly show "Suspended".
            // Implementation: Redirect to /account-suspended.
            
            return redirect()->route('account.suspended');
        }

        return $next($request);
    }
}

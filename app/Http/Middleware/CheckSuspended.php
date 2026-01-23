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
            // Allow access to logout and the suspension page itself (if we used a named route)
            // But usually easiest to just check if they are visiting anything other than public portal
            // or if the URL is not the suspension error page.
            
            if (!$request->is('*logout*') && !$request->is('*login*')) {
                return response()->view('errors.account-suspended', [], 403);
            }
        }

        return $next($request);
    }
}

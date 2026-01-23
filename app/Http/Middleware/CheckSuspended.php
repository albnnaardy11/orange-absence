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
            // Only redirect if not already on the suspended page
            if (!$request->is('account-suspended')) {
                return response()->view('errors.account-suspended', [], 403);
            }
        }

        return $next($request);
    }
}

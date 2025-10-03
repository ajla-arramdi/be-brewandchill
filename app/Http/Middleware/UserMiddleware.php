<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || (!$request->user()->hasRole('user') && !$request->user()->hasRole('admin') && !$request->user()->hasRole('cashier'))) {
            return response()->json(['message' => 'User access required'], 403);
        }

        return $next($request);
    }
}
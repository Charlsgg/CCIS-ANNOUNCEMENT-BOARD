<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     * * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Check if the user is authenticated
        if (!Auth::check()) {
            return redirect('/');
        }

        // 2. Check if the authenticated user's type matches the required role
        if (Auth::user()->user_type !== $role) {
            abort(403, 'Unauthorized action. You do not belong to this department.');

        }

        // 3. If they match, allow the request to proceed
        return $next($request);
    }
}   
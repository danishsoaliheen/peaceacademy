<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Check if the authenticated user has one of the allowed roles.
     *
     * Usage in routes: ->middleware('role:admin,accountant')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role ?? 'viewer';

        if (!in_array($userRole, $roles)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
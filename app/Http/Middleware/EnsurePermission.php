<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Check whether the authenticated user has the required permission.
     *
     * Usage:
     *
     * ->middleware('permission:students.create')
     *
     * Multiple permissions can also be supplied:
     *
     * ->middleware('permission:students.create,students.edit')
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$permissions
    ): Response {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->hasAnyPermission($permissions)) {
            abort(
                403,
                'You do not have permission to perform this action.'
            );
        }

        return $next($request);
    }
}
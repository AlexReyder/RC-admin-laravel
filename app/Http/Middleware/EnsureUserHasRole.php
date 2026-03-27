<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $currentRole = $user->role?->value ?? $user->role;

        if (! in_array($currentRole, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
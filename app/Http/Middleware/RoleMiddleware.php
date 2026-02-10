<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:1') or ->middleware('role:1,2')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $allowed = collect($roles)
            ->filter(fn($r) => $r !== null && $r !== '')
            ->map(fn($r) => (int) $r)
            ->values();

        if ($allowed->isEmpty()) {
            return $next($request);
        }

        $userRole = (int) ($user->role ?? 0);

        if (!$allowed->contains($userRole)) {
            abort(403);
        }

        return $next($request);
    }
}

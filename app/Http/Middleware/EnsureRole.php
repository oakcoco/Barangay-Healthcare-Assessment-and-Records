<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $role ??= $request->segment(1);   // 'resident' or 'bhw', taken from the URL

        $user = $request->user();

        if ($user->status !== 'approved') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login');
        }

        $home = match ($user->role) {
            'bhw' => '/bhw/dashboard',
            'resident' => '/resident/home',
            default => null,
        };

        if ($home === null) {
            abort(403);
        }

        if ($user->role !== $role) {
            return redirect($home);
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
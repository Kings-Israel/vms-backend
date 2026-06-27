<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isTwoFactorEnabled()) {
            // Exempt routes
            $exempt = [
                '2fa/setup', '2fa/enable', '2fa/confirm', 'logout',
            ];

            foreach ($exempt as $path) {
                if ($request->is($path)) {
                    return $next($request);
                }
            }

            return redirect()->route('2fa.setup')
                ->with('warning', 'You must enable two-factor authentication to access the dashboard.');
        }

        return $next($request);
    }
}

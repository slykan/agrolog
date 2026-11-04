<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bilježi kad je prijavljeni korisnik zadnji put bio u aplikaciji (users.last_seen_at),
 * najviše jednom u 10 minuta da ne piše u bazu na svaki zahtjev.
 */
class ZabiljeziAktivnost
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinutes(10)))) {
            $user->forceFill(['last_seen_at' => now()])->saveQuietly();
        }

        return $next($request);
    }
}

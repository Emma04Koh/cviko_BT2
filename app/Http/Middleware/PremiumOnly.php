<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PremiumOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->premium_until || $user->premium_until->isPast()) {
            return response()->json([
                'message' => 'Táto operácia je dostupná iba prémiovým používateľom.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

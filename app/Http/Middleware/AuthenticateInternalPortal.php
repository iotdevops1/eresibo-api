<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateInternalPortal
{
    public function handle(Request $request, Closure $next): Response {

        $providedKey = $request->header('X-Eresibo-Internal-Key');

        $configuredKey = config(
            'eresibo.internal.portal_key'
        );

        if (! $providedKey || ! $configuredKey || ! hash_equals(
                $configuredKey,
                $providedKey
            )
        )
        {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
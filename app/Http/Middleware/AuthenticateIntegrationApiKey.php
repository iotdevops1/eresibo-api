<?php

namespace App\Http\Middleware;

use App\Models\IntegrationApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateIntegrationApiKey
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $apiKey = $request->header('X-API-Key');

        if (! $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Missing API key.',
            ], 401);
        }

        $keyHash = hash('sha256', $apiKey);

        $integrationKey = IntegrationApiKey::query()
            ->where('key_hash', $keyHash)
            ->where('active', true)
            ->first();

        if (
            ! $integrationKey ||
            ! $integrationKey->isValid()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API key.',
            ], 401);
        }

        $integrationKey->forceFill([
            'last_used_at' => now(),
        ])->save();

        $request->attributes->set(
            'integration_api_key',
            $integrationKey
        );

        return $next($request);
    }
}
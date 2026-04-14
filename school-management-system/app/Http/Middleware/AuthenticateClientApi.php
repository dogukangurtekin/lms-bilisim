<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\UserProfile;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);
        $uid = $this->extractUid($request);
        if ($token === '' || $uid === '') {
            logger()->warning('client_api_unauthenticated:missing', [
                'uid' => $uid,
                'has_token' => $token !== '',
                'path' => $request->path(),
            ]);
            return new JsonResponse(['message' => 'unauthenticated'], 401);
        }

        $cacheKey = "client_api_token:{$uid}";
        $storedHash = (string) Cache::get($cacheKey, '');
        $incomingHash = hash('sha256', $token);
        if ($storedHash === '' || ! hash_equals($storedHash, $incomingHash)) {
            $profileHash = $this->resolveProfileTokenHash($uid);
            if ($profileHash !== '' && hash_equals($profileHash, $incomingHash)) {
                // Cache miss veya temizlenme durumlarında profildeki hash'i kabul et.
                Cache::put($cacheKey, $profileHash, now()->addMinutes(max(30, (int) env('CLIENT_API_TOKEN_TTL_MINUTES', 1440))));
            } else {
            logger()->warning('client_api_unauthenticated:token_mismatch', [
                'uid' => $uid,
                'stored_hash_prefix' => substr($storedHash, 0, 10),
                'profile_hash_prefix' => substr($profileHash, 0, 10),
                'incoming_hash_prefix' => substr($incomingHash, 0, 10),
                'path' => $request->path(),
            ]);
            return new JsonResponse(['message' => 'unauthenticated'], 401);
            }
        }

        $request->attributes->set('client_uid', $uid);
        $request->attributes->set('client_token_hash', $incomingHash);

        return $next($request);
    }

    private function extractToken(Request $request): string
    {
        $bearer = (string) $request->bearerToken();
        if ($bearer !== '') return $bearer;
        return trim((string) $request->header('X-Client-Token', ''));
    }

    private function extractUid(Request $request): string
    {
        $candidates = [
            $request->header('X-Client-Uid'),
            $request->input('uid'),
            $request->query('uid'),
        ];
        foreach ($candidates as $candidate) {
            $uid = trim((string) $candidate);
            if ($uid !== '') return $uid;
        }
        return '';
    }

    private function resolveProfileTokenHash(string $uid): string
    {
        if ($uid === '') return '';
        try {
            $profile = UserProfile::query()->where('user_id', $uid)->first();
            if (! $profile) return '';
            $meta = $profile->meta;
            if (is_string($meta) && $meta !== '') {
                $decoded = json_decode($meta, true);
                $meta = is_array($decoded) ? $decoded : [];
            }
            if (! is_array($meta)) $meta = [];
            $hash = (string) ($meta['clientApiTokenHash'] ?? '');
            return trim($hash);
        } catch (\Throwable $e) {
            logger()->warning('client_api_token_meta_read_failed', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);
        }
        return '';
    }
}

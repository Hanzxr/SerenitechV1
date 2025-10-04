<?php
namespace App\Services;

use Firebase\JWT\JWT;

class JitsiTokenService
{
    public function generateToken(string $room, array $userPayload = [], int $ttl = 3600): string
    {
        $appId = env('JITSI_APP_ID');
        $secret = env('JITSI_APP_SECRET');
        $domain = env('JITSI_DOMAIN');

        $now = time();
        $payload = [
            'aud' => 'jitsi',
            'iss' => $appId,
            'sub' => $domain,
            'room' => $room,
            'exp' => $now + $ttl,
            'nbf' => $now - 10,
            'context' => [
                'user' => $userPayload
            ],
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }
}

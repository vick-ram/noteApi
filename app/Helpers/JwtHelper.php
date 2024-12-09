<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class JwtHelper
{

    private static $secretKey;

    // Static constructor to initialize the secret key
    public static function init()
    {
        self::$secretKey = getenv("JWT_SECRET_KEY");
    }

    // Helper function to encode base64
    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Helper function to decode base64
    public static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    public static function generateToken(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'type' => 'JWT',
        ];

        $payload['iss'] = 'note api';
        $payload['iat'] = now()->timestamp;
        $payload['exp'] = time() + 3600;
        $payload['jti'] = Str::uuid()->toString();

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // create the signature
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::$secretKey, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        // Return the full token
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function validateToken(string $token): ?array
    {
        $parts = explode(".", $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;

        $signatureExpected = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            self::$secretKey,
            true
        );

        $signatureExpectedEncoded = self::base64UrlEncode($signatureExpected);

        if (!hash_equals($signatureExpectedEncoded, $signatureProvided)) {
            return null;
        }

        // Decode the payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded));

        // Check claims
        if (isset($payload['exp']) && $payload['exp' < time()]) {
            return null;
        }

        return $payload;
    }
}

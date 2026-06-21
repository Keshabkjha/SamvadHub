<?php

namespace App\Helpers;

class GoogleJwtVerifier {
    private static string $certsUrl = 'https://www.googleapis.com/oauth2/v1/certs';
    private static string $clientId = '112654151448256936934';

    /**
     * Decodes and cryptographically verifies a Google ID Token (JWT).
     * Returns the payload array if valid, or null if invalid/forged.
     */
    public static function verify(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header64, $payload64, $signature64] = $parts;

        $header = json_decode(self::base64UrlDecode($header64), true);
        $payload = json_decode(self::base64UrlDecode($payload64), true);
        $signature = self::base64UrlDecode($signature64);

        if (!$header || !$payload || !$signature) {
            return null;
        }

        // Verify key ID (kid) exists in header
        $kid = $header['kid'] ?? '';
        if (empty($kid)) {
            return null;
        }

        $certs = self::fetchCerts();
        if (!isset($certs[$kid])) {
            return null;
        }

        $publicKey = $certs[$kid];

        // Verify cryptographic signature
        $dataToSign = "$header64.$payload64";
        $ok = openssl_verify($dataToSign, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            return null;
        }

        // Verify Issuer (iss)
        $iss = $payload['iss'] ?? '';
        if ($iss !== 'accounts.google.com' && $iss !== 'https://accounts.google.com') {
            return null;
        }

        // Verify Audience (aud)
        $aud = $payload['aud'] ?? '';
        if ($aud !== self::$clientId) {
            return null;
        }

        // Verify Expiration time (exp)
        $exp = $payload['exp'] ?? 0;
        if ($exp < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Base64URL decoding helper.
     */
    private static function base64UrlDecode(string $input): string {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Fetch and cache Google's public PEM certificates.
     */
    private static function fetchCerts(): array {
        if (isset($_SESSION['google_certs']) && isset($_SESSION['google_certs_expire']) && $_SESSION['google_certs_expire'] > time()) {
            return $_SESSION['google_certs'];
        }

        $ch = curl_init(self::$certsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Strict SSL verification
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return [];
        }

        $certs = json_decode($response, true);
        if (is_array($certs)) {
            $_SESSION['google_certs'] = $certs;
            $_SESSION['google_certs_expire'] = time() + 3600; // Cache for 1 hour
            return $certs;
        }

        return [];
    }
}

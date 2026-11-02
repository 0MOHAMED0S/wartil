<?php

namespace App\Services;

use Exception;

/**
 * Simple GeoIP service to resolve an IP address to a two‑letter ISO country code.
 * It uses a public API (ipapi.co) and returns null on failure.
 */
class GeoIPService
{
    /**
     * Retrieve the ISO‑3166‑1 alpha‑2 country code for the given IP address.
     *
     * @param string $ip The client IP address.
     * @return string|null Upper‑case country code (e.g. "EG", "SA") or null on failure.
     */
    public static function getCountryCode(string $ip): ?string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }
        $url = "https://ipapi.co/{$ip}/country/";
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'ignore_errors' => true,
                ]
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }
            $code = trim($response);
            return preg_match('/^[A-Za-z]{2}$/', $code) ? strtoupper($code) : null;
        } catch (Exception $e) {
            return null;
        }
    }
}

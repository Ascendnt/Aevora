<?php

namespace App\Libraries;

use Config\Services;
use Throwable;

/**
 * Resolves the IANA timezone for a clock in/out punch from the employee's
 * connection (their IP address) rather than assuming the server's own
 * timezone — a Railway container has no inherent "local" time for an
 * employee working from Australia or the Philippines.
 *
 * Uses ip-api.com's free tier (no API key, ~45 req/min) which returns a
 * timezone name directly — no separate country-to-timezone mapping needed.
 * Server-to-server call, so the free tier's HTTP-only endpoint is fine even
 * though this app itself is served over HTTPS (that mixed-content rule only
 * applies to browser requests, not backend curl calls).
 *
 * Falls back to a small per-country default (covering the countries this
 * app already ships payroll reference data for) when geolocation fails —
 * e.g. local development, a private/loopback IP, or the API being unreachable.
 */
class GeoTimeService
{
    private const COUNTRY_FALLBACK_TZ = [
        'PH' => 'Asia/Manila',
        'AU' => 'Australia/Sydney',
    ];

    /** @return string|null An IANA timezone name (e.g. "Asia/Manila"), or null if it can't be determined at all. */
    public function resolveTimezoneForIp(string $ip): ?string
    {
        if ($ip === '' || $this->isPrivateOrLoopback($ip)) {
            return null;
        }

        try {
            $client   = Services::curlrequest();
            $response = $client->get("http://ip-api.com/json/{$ip}", [
                'query'   => ['fields' => 'status,timezone'],
                'timeout' => 4,
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = json_decode((string) $response->getBody(), true);

            if (! is_array($data) || ($data['status'] ?? '') !== 'success' || empty($data['timezone'])) {
                return null;
            }

            return (string) $data['timezone'];
        } catch (Throwable) {
            // Geolocation is a nice-to-have, never a hard requirement to clock in/out.
            return null;
        }
    }

    /** Best-effort timezone for a punch: geo-detected first, then the company's own country, then UTC. */
    public function resolveForRequest(string $ip, ?string $companyCountryCode): string
    {
        return $this->resolveTimezoneForIp($ip)
            ?? self::COUNTRY_FALLBACK_TZ[$companyCountryCode] ?? null
            ?? 'UTC';
    }

    private function isPrivateOrLoopback(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}

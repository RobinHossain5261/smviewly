<?php

namespace App\Services;

use App\Exceptions\SocialLookupException;
use App\Models\SiteSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialApiClient
{
    public function lookup(string $api, array $payload): array
    {
        $base = rtrim((string) SiteSetting::get('social_api_base_url', 'https://data.checkmyalgo.com'), '/');
        $token = (string) SiteSetting::get('social_api_token', '');

        if ($base === '' || $token === '') {
            throw SocialLookupException::notConfigured();
        }

        $url = $base.'/api/v1/'.$api.'/';

        // LinkedIn blocks aggressively (HTTP 999) — always route it through the proxy
        // when the admin enabled it, and default LinkedIn to proxy when unset.
        $useProxy = SiteSetting::get('social_api_use_proxy', $api === 'linkedin' ? '1' : '0');
        if ($useProxy === '1' || $api === 'linkedin') {
            $payload['use_proxy'] = true;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(90)
                ->connectTimeout(10)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::warning('Social API connection failed', ['api' => $api, 'error' => $e->getMessage()]);
            throw SocialLookupException::failed('Could not reach the social data API. Please try again.');
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw SocialLookupException::failed('Unexpected response from the social data API.');
        }

        if ($response->status() === 401) {
            throw SocialLookupException::failed('API token is invalid. Update it in Admin → Settings.');
        }

        if ($response->failed() && empty($json['success'])) {
            $message = $json['error'] ?? $json['detail'] ?? $this->firstValidationError($json);
            throw SocialLookupException::failed((string) $message);
        }

        return $json;
    }

    /**
     * Fire several section lookups (posts, photos, reels, …) in parallel and
     * return only the successful ones, keyed by the payload key.
     */
    public function lookupMany(string $api, array $payloads): array
    {
        $base = rtrim((string) SiteSetting::get('social_api_base_url', 'https://data.checkmyalgo.com'), '/');
        $token = (string) SiteSetting::get('social_api_token', '');

        if ($base === '' || $token === '') {
            throw SocialLookupException::notConfigured();
        }

        $url = $base.'/api/v1/'.$api.'/';

        $useProxy = SiteSetting::get('social_api_use_proxy', $api === 'linkedin' ? '1' : '0');
        if ($useProxy === '1' || $api === 'linkedin') {
            foreach ($payloads as $key => $payload) {
                $payloads[$key]['use_proxy'] = true;
            }
        }

        $responses = Http::pool(function ($pool) use ($url, $payloads, $token) {
            $calls = [];
            foreach ($payloads as $key => $payload) {
                $calls[$key] = $pool->as($key)->withToken($token)
                    ->acceptJson()
                    ->timeout(90)
                    ->connectTimeout(10)
                    ->post($url, $payload);
            }

            return $calls;
        });

        $out = [];
        foreach ($responses as $key => $response) {
            if (! $response instanceof \Illuminate\Http\Client\Response || ! $response->ok()) {
                continue;
            }
            $json = $response->json();
            if (! is_array($json)) {
                continue;
            }
            if (! empty($json['requires_login']) || (isset($json['success']) && $json['success'] === false)) {
                continue;
            }
            $out[$key] = $json;
        }

        return $out;
    }

    private function firstValidationError(array $json): string
    {
        foreach ($json as $value) {
            if (is_array($value) && isset($value[0]) && is_string($value[0])) {
                return $value[0];
            }
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return 'Lookup failed.';
    }
}

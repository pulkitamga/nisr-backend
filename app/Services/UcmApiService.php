<?php

namespace App\Services;

use App\Utils\Helpers;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class UcmApiService
{
    private const DEFAULT_API_VERSION = '1.0';
    private const COOKIE_TTL_MINUTES = 9;

    private ?Client $client = null;
    private array $config = [];
    private ?string $cookie = null;
    private bool $useDigestFallback = false;

    public function __construct()
    {
        $this->config = Helpers::ucmConfig();
        if (!$this->isConfigured()) {
            return;
        }

        $verify = false;
        $caPath = trim((string)($this->config['ca_path'] ?? ''));
        if ($caPath !== '' && file_exists($caPath)) {
            $verify = $caPath;
        } else {
            $verify = (bool)($this->config['verify_tls'] ?? false);
        }

        $this->client = new Client([
            'base_uri' => sprintf('https://%s:%d/api', $this->config['host'], $this->config['port']),
            'timeout'  => 8,
            'verify'   => $verify,
        ]);
    }

    public function isAvailable(): bool
    {
        return $this->client instanceof Client;
    }

    public function request(array $payload, bool $retryOnAuthFailure = true): array
    {
        if (!$this->ensureAuthenticated()) {
            return ['status' => -5, 'response' => []];
        }

        if (!$this->useDigestFallback && $this->cookie) {
            $payload['cookie'] = $this->cookie;
        }
        $response = $this->requestRaw($payload);
        $status = (int)($response['status'] ?? -9);

        if ($this->useDigestFallback) {
            return $response;
        }

        if ($retryOnAuthFailure && in_array($status, [-5, -6], true)) {
            $this->clearCookie();
            if ($this->ensureAuthenticated()) {
                if (!$this->useDigestFallback && $this->cookie) {
                    $payload['cookie'] = $this->cookie;
                } else {
                    unset($payload['cookie']);
                }
                return $this->requestRaw($payload);
            }
        }

        return $response;
    }

    public function listUnBridgedChannels(): array
    {
        $res = $this->request(['action' => 'listUnBridgedChannels']);
        $channels = $res['response']['channel'] ?? [];
        return is_array($channels) ? $channels : [];
    }

    public function listBridgedChannels(): array
    {
        $res = $this->request(['action' => 'listBridgedChannels']);
        $channels = $res['response']['channel'] ?? [];
        return is_array($channels) ? $channels : [];
    }

    public function getLiveCalls(): array
    {
        $indexedCalls = [];

        foreach ($this->listUnBridgedChannels() as $call) {
            $normalized = $this->normalizeUnbridgedCall($call);
            if ($normalized === null) {
                continue;
            }
            $indexedCalls[$normalized['call_id']] = $normalized;
        }

        foreach ($this->listBridgedChannels() as $call) {
            $normalized = $this->normalizeBridgedCall($call);
            if ($normalized === null) {
                continue;
            }
            $indexedCalls[$normalized['call_id']] = $normalized;
        }

        return array_values($indexedCalls);
    }

    public function acceptCall(string $channel): array
    {
        return $this->request([
            'action' => 'acceptCall',
            'channel' => $channel,
        ]);
    }

    public function rejectCall(string $channel): array
    {
        return $this->request([
            'action' => 'refuseCall',
            'channel' => $channel,
        ]);
    }

    public function hangupCall(string $channel): array
    {
        return $this->request([
            'action' => 'Hangup',
            'channel' => $channel,
        ]);
    }

    private function isConfigured(): bool
    {
        return (bool)($this->config['status'] ?? false)
            && !empty($this->config['host'])
            && !empty($this->config['username'])
            && !empty($this->config['password']);
    }

    private function cookieCacheKey(): string
    {
        $cacheSeed = sprintf(
            '%s:%s:%s',
            $this->config['host'] ?? '',
            $this->config['port'] ?? '',
            $this->config['username'] ?? ''
        );

        return 'ucm_api_cookie:' . sha1($cacheSeed);
    }

    private function ensureAuthenticated(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if ($this->cookie) {
            return true;
        }

        if ($this->useDigestFallback && !empty($this->config['digest'])) {
            return true;
        }

        $cachedCookie = Cache::get($this->cookieCacheKey());
        if (is_string($cachedCookie) && $cachedCookie !== '') {
            $this->cookie = $cachedCookie;
            return true;
        }

        $challengeResponse = $this->requestRaw([
            'action' => 'challenge',
            'user' => (string)$this->config['username'],
            'version' => (string)($this->config['api_version'] ?? self::DEFAULT_API_VERSION),
        ]);

        $challenge = $challengeResponse['response']['challenge'] ?? null;
        if ((int)($challengeResponse['status'] ?? -9) !== 0 || !is_string($challenge) || $challenge === '') {
            if (!empty($this->config['digest'])) {
                $this->useDigestFallback = true;
                Log::warning('UCM challenge failed, falling back to digest auth mode', [
                    'status' => $challengeResponse['status'] ?? null,
                ]);
                return true;
            }

            Log::warning('UCM challenge request failed', [
                'status' => $challengeResponse['status'] ?? null,
            ]);
            return false;
        }

        $loginPayload = [
            'action' => 'login',
            'user' => (string)$this->config['username'],
            'token' => md5($challenge . (string)$this->config['password']),
        ];

        $reportUrl = trim((string)($this->config['report_url'] ?? ''));
        if ($reportUrl !== '') {
            $loginPayload['url'] = $reportUrl;
        }

        $loginResponse = $this->requestRaw($loginPayload);
        $cookie = $loginResponse['response']['cookie'] ?? null;
        if ((int)($loginResponse['status'] ?? -9) !== 0 || !is_string($cookie) || $cookie === '') {
            if (!empty($this->config['digest'])) {
                $this->useDigestFallback = true;
                Log::warning('UCM login failed, falling back to digest auth mode', [
                    'status' => $loginResponse['status'] ?? null,
                ]);
                return true;
            }

            Log::warning('UCM login failed', [
                'status' => $loginResponse['status'] ?? null,
            ]);
            return false;
        }

        $this->cookie = $cookie;
        Cache::put($this->cookieCacheKey(), $cookie, now()->addMinutes(self::COOKIE_TTL_MINUTES));
        return true;
    }

    private function clearCookie(): void
    {
        $this->cookie = null;
        Cache::forget($this->cookieCacheKey());
    }

    private function requestRaw(array $payload): array
    {
        if (!$this->isAvailable()) {
            return ['status' => -1, 'response' => []];
        }

        $options = ['json' => ['request' => $payload]];
        if ($this->useDigestFallback && !empty($this->config['digest'])) {
            $options['auth'] = [
                (string)$this->config['username'],
                (string)$this->config['password'],
                'digest',
            ];
        }

        try {
            $response = $this->client->post('', $options);
            $decoded = json_decode((string)$response->getBody(), true);
            return is_array($decoded) ? $decoded : ['status' => -9, 'response' => []];
        } catch (Throwable $exception) {
            Log::error('UCM API request failed', [
                'error' => $exception->getMessage(),
                'action' => $payload['action'] ?? null,
            ]);
            return ['status' => -9, 'response' => []];
        }
    }

    private function normalizeUnbridgedCall(array $call): ?array
    {
        $channel = trim((string)($call['channel'] ?? ''));
        if ($channel === '') {
            return null;
        }

        $state = strtolower((string)($call['state'] ?? 'ringing'));
        $normalizedState = str_contains($state, 'ring') ? 'ringing' : 'ongoing';
        $caller = trim((string)($call['callernum'] ?? ''));
        $callee = trim((string)($call['connectednum'] ?? ''));

        $callId = trim((string)($call['uniqueid'] ?? ''));
        if ($callId === '') {
            $callId = sha1($channel . '|' . $caller . '|' . $callee);
        }

        return [
            'call_id' => $callId,
            'channel' => $channel,
            'peer_channel' => '',
            'caller' => $caller,
            'callee' => $callee,
            'status' => $normalizedState,
            'direction' => !empty($call['inbound_trunk_name']) ? 'inbound' : 'outbound',
            'started_at' => (string)($call['alloc_time'] ?? ''),
            'ucm_uniqueid' => (string)($call['uniqueid'] ?? ''),
            'ucm_bridge_id' => '',
            'raw' => $call,
        ];
    }

    private function normalizeBridgedCall(array $call): ?array
    {
        $channel = trim((string)($call['channel1'] ?? ''));
        if ($channel === '') {
            return null;
        }

        $caller = trim((string)($call['callerid1'] ?? ''));
        $callee = trim((string)($call['callerid2'] ?? ''));

        $callId = trim((string)($call['uniqueid1'] ?? $call['bridge_id'] ?? ''));
        if ($callId === '') {
            $callId = sha1($channel . '|' . ($call['channel2'] ?? '') . '|' . $caller . '|' . $callee);
        }

        return [
            'call_id' => $callId,
            'channel' => $channel,
            'peer_channel' => (string)($call['channel2'] ?? ''),
            'caller' => $caller,
            'callee' => $callee,
            'status' => 'ongoing',
            'direction' => !empty($call['inbound_trunk_name']) ? 'inbound' : 'outbound',
            'started_at' => (string)($call['bridge_time'] ?? ''),
            'ucm_uniqueid' => (string)($call['uniqueid1'] ?? ''),
            'ucm_bridge_id' => (string)($call['bridge_id'] ?? ''),
            'raw' => $call,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CrmCall;
use App\Models\User;
use App\Utils\Helpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UcmWebhookController extends Controller
{
    private const WEBHOOK_HEARTBEAT_KEY = 'ucm:webhook:last_heartbeat';

    public function handle(Request $request): JsonResponse
    {
        $config = Helpers::ucmConfig();
        if (empty($config['status'])) {
            return response()->json(['ok' => false, 'message' => 'UCM integration disabled'], 503);
        }

        $token = trim((string)($config['webhook_token'] ?? ''));
        if ($token !== '' && !$this->isAuthorized($request, $token)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $payload = $this->parsePayload($request);
        $events = $this->extractEvents($payload);

        $processed = 0;
        foreach ($events as $event) {
            if ($this->processEvent($event)) {
                $processed++;
            }
        }

        // Update heartbeat to indicate webhooks are working
        if ($processed > 0) {
            Cache::put(self::WEBHOOK_HEARTBEAT_KEY, now()->toIso8601String(), now()->addMinutes(5));
        }

        return response()->json(['ok' => true, 'processed' => $processed]);
    }

    private function isAuthorized(Request $request, string $expectedToken): bool
    {
        $providedToken = (string)($request->header('X-UCM-Webhook-Token')
            ?? $request->input('token')
            ?? '');

        return $providedToken !== '' && hash_equals($expectedToken, $providedToken);
    }

    private function parsePayload(Request $request): array
    {
        $input = $request->all();
        if (!empty($input)) {
            return $input;
        }

        $body = trim((string)$request->getContent());
        if ($body === '') {
            return [];
        }

        $decodedJson = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedJson)) {
            return $decodedJson;
        }

        if (Str::startsWith($body, '<')) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml !== false) {
                $json = json_encode($xml);
                $decodedXml = json_decode((string)$json, true);
                if (is_array($decodedXml)) {
                    return $decodedXml;
                }
            }
        }

        parse_str($body, $parsed);
        return is_array($parsed) ? $parsed : [];
    }

    private function extractEvents(array $payload): array
    {
        if (isset($payload['events']) && is_array($payload['events'])) {
            return array_values(array_filter($payload['events'], 'is_array'));
        }

        if (array_is_list($payload)) {
            return array_values(array_filter($payload, 'is_array'));
        }

        return empty($payload) ? [] : [$payload];
    }

    private function processEvent(array $event): bool
    {
        $channel = trim((string)($event['channel'] ?? $event['channel1'] ?? ''));
        $caller = $this->normalizeDigits((string)($event['caller'] ?? $event['callernum'] ?? $event['callerid1'] ?? $event['src'] ?? ''));
        $callee = $this->normalizeDigits((string)($event['callee'] ?? $event['connectednum'] ?? $event['callerid2'] ?? $event['dst'] ?? ''));

        if ($channel === '' && $caller === '' && $callee === '') {
            return false;
        }

        $status = $this->resolveStatus($event);
        $callId = $this->resolveCallId($event, $channel, $caller, $callee);
        $startedAt = $this->resolveDate($event['started_at'] ?? $event['event_time'] ?? $event['time'] ?? null);
        $direction = !empty($event['inbound_trunk_name']) ? 'inbound' : 'outbound';
        $contact = $this->resolveContact($caller, $callee);

        $crmCall = CrmCall::updateOrCreate(
            ['call_id' => $callId],
            [
                'customer_id' => $contact?->id,
                'call_date' => $startedAt,
                'started_at' => $startedAt,
                'answered_at' => $status === 'ongoing' ? now() : null,
                'ended_at' => $status === 'completed' ? now() : null,
                'direction' => $direction,
                'status' => $status,
                'ucm_channel' => $channel,
                'ucm_peer_channel' => (string)($event['channel2'] ?? ''),
                'ucm_uniqueid' => (string)($event['uniqueid'] ?? $event['uniqueid1'] ?? ''),
                'ucm_bridge_id' => (string)($event['bridge_id'] ?? ''),
                'src_number' => $caller,
                'dst_number' => $callee,
                'raw_payload' => $event,
            ]
        );

        if ($status === 'completed') {
            $crmCall->update([
                'call_duration' => now()->diffInSeconds($crmCall->call_date),
                'ended_at' => now(),
            ]);
        }

        return true;
    }

    private function resolveCallId(array $event, string $channel, string $caller, string $callee): string
    {
        $callId = trim((string)($event['call_id'] ?? $event['uniqueid'] ?? $event['uniqueid1'] ?? $event['session'] ?? $event['bridge_id'] ?? ''));
        if ($callId !== '') {
            return $callId;
        }

        return sha1($channel . '|' . $caller . '|' . $callee . '|' . json_encode($event));
    }

    private function resolveStatus(array $event): string
    {
        $statusHint = strtolower(implode(' ', array_filter([
            (string)($event['event'] ?? ''),
            (string)($event['event_type'] ?? ''),
            (string)($event['action'] ?? ''),
            (string)($event['type'] ?? ''),
            (string)($event['state'] ?? ''),
        ])));

        if (str_contains($statusHint, 'hangup') || str_contains($statusHint, 'end') || str_contains($statusHint, 'destroy')) {
            return 'completed';
        }

        if (
            str_contains($statusHint, 'bridge')
            || str_contains($statusHint, 'answer')
            || str_contains($statusHint, 'accept')
            || str_contains($statusHint, 'up')
            || str_contains($statusHint, 'ongoing')
        ) {
            return 'ongoing';
        }

        return 'ringing';
    }

    private function resolveDate(mixed $date): Carbon
    {
        if (is_string($date) && trim($date) !== '') {
            try {
                return Carbon::parse($date);
            } catch (\Throwable) {
                // Keep default now.
            }
        }

        return now();
    }

    private function resolveContact(string $caller, string $callee): ?User
    {
        $numbers = array_values(array_filter(array_unique([$caller, $callee])));
        if (empty($numbers)) {
            return null;
        }

        $contact = User::query()
            ->whereIn('phone', $numbers)
            ->first();
        if ($contact) {
            return $contact;
        }

        $matches = User::query()
            ->where(function ($query) use ($numbers) {
                foreach ($numbers as $number) {
                    $query->orWhere('phone', 'like', '%' . $number . '%');
                }
            })
            ->get();

        foreach ($matches as $user) {
            if (in_array($this->normalizeDigits((string)$user->phone), $numbers, true)) {
                return $user;
            }
        }

        return null;
    }

    private function normalizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * Check if webhooks are actively being received.
     * Returns true if a webhook was received within the threshold.
     */
    public static function isWebhookAlive(int $thresholdSeconds = 30): bool
    {
        $lastHeartbeat = Cache::get(self::WEBHOOK_HEARTBEAT_KEY);
        if (!$lastHeartbeat) {
            return false;
        }

        try {
            $lastTime = Carbon::parse($lastHeartbeat);
            return $lastTime->gt(now()->subSeconds($thresholdSeconds));
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get the last webhook heartbeat timestamp.
     */
    public static function getLastHeartbeat(): ?string
    {
        return Cache::get(self::WEBHOOK_HEARTBEAT_KEY);
    }
}

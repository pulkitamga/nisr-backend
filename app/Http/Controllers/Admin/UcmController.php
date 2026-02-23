<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCall;
use App\Models\User;
use App\Services\UcmApiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UcmController extends Controller
{
    public function calls(): JsonResponse
    {
        $ucm = $this->ucm();
        if (!$ucm->isAvailable()) {
            return response()->json([]);
        }

        $activeCalls = $ucm->getLiveCalls();
        $adminUser = auth('admin')->user();
        $agentId = (int)($adminUser->id ?? 0);
        $employeeExtension = $this->normalizeDigits((string)($adminUser->extension ?? ''));

        $calls = collect($activeCalls)->map(function (array $call) use ($employeeExtension, $agentId) {
            $caller = $this->normalizeDigits((string)($call['caller'] ?? ''));
            $callee = $this->normalizeDigits((string)($call['callee'] ?? ''));
            $callId = (string)($call['call_id'] ?? '');
            $channel = (string)($call['channel'] ?? '');

            $contact = $this->resolveContact($caller, $callee);
            $isMine = $employeeExtension === ''
                ? true
                : in_array($employeeExtension, [$caller, $callee], true);

            if ($isMine) {
                $this->upsertCrmCall($call, $contact, $agentId);
            }

            $status = (string)($call['status'] ?? 'ringing');
            return [
                'call_id' => $callId,
                'channel' => $channel,
                'caller' => $caller,
                'callee' => $callee,
                'status' => $status,
                'contact' => $contact ? ['name' => $contact->name] : null,
                'is_mine' => $isMine,
            ];
        })->values();

        return response()->json($calls);
    }

    public function accept(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'string'],
            'call_id' => ['nullable', 'string'],
        ]);

        $ucmResponse = $this->ucm()->acceptCall($validated['channel']);
        $isSuccess = (int)($ucmResponse['status'] ?? -9) === 0;
        if ($isSuccess) {
            $this->updateCallStatus($validated['call_id'] ?? null, 'ongoing');
        }

        return response()->json([
            'ok' => $isSuccess,
            'status' => $isSuccess ? 'accepted' : 'failed',
            'ucm_status' => (int)($ucmResponse['status'] ?? -9),
        ]);
    }

    public function reject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'string'],
            'call_id' => ['nullable', 'string'],
        ]);

        $ucmResponse = $this->ucm()->rejectCall($validated['channel']);
        $isSuccess = (int)($ucmResponse['status'] ?? -9) === 0;
        if ($isSuccess) {
            $this->updateCallStatus($validated['call_id'] ?? null, 'completed');
        }

        return response()->json([
            'ok' => $isSuccess,
            'status' => $isSuccess ? 'rejected' : 'failed',
            'ucm_status' => (int)($ucmResponse['status'] ?? -9),
        ]);
    }

    public function end(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'string'],
            'call_id' => ['nullable', 'string'],
        ]);

        $ucmResponse = $this->ucm()->hangupCall($validated['channel']);
        $isSuccess = (int)($ucmResponse['status'] ?? -9) === 0;
        if ($isSuccess) {
            $this->updateCallStatus($validated['call_id'] ?? null, 'completed');
        }

        return response()->json([
            'ok' => $isSuccess,
            'status' => $isSuccess ? 'ended' : 'failed',
            'ucm_status' => (int)($ucmResponse['status'] ?? -9),
        ]);
    }

    private function upsertCrmCall(array $call, ?User $contact, int $agentId): void
    {
        $callId = (string)($call['call_id'] ?? '');
        if ($callId === '') {
            return;
        }

        $startedAt = $this->resolveCallDate($call['started_at'] ?? null);
        $callStatus = (string)($call['status'] ?? 'ringing');
        $channel = trim((string)($call['channel'] ?? ''));
        $caller = $this->normalizeDigits((string)($call['caller'] ?? ''));
        $callee = $this->normalizeDigits((string)($call['callee'] ?? ''));

        CrmCall::updateOrCreate(
            ['call_id' => $callId],
            [
                'customer_id' => $contact?->id,
                'agent_id' => $agentId ?: null,
                'call_date' => $startedAt,
                'started_at' => $startedAt,
                'answered_at' => $callStatus === 'ongoing' ? now() : null,
                'ended_at' => $callStatus === 'completed' ? now() : null,
                'call_duration' => 0,
                'call_notes' => $channel ? "UCM channel: {$channel}" : null,
                'direction' => (string)($call['direction'] ?? 'inbound'),
                'status' => $callStatus,
                'ucm_channel' => $channel,
                'ucm_peer_channel' => (string)($call['peer_channel'] ?? ''),
                'ucm_uniqueid' => (string)($call['ucm_uniqueid'] ?? ''),
                'ucm_bridge_id' => (string)($call['ucm_bridge_id'] ?? ''),
                'src_number' => $caller,
                'dst_number' => $callee,
                'raw_payload' => $call['raw'] ?? null,
            ]
        );
    }

    private function resolveCallDate(mixed $date): Carbon
    {
        if (is_string($date) && trim($date) !== '') {
            try {
                return Carbon::parse($date);
            } catch (\Throwable) {
                // Fall through to now() on parse failure.
            }
        }

        return now();
    }

    private function updateCallStatus(?string $callId, string $status): void
    {
        if (!$callId) {
            return;
        }

        $call = CrmCall::where('call_id', $callId)->first();
        if (!$call) {
            return;
        }

        $payload = ['status' => $status];
        if ($status === 'ongoing' && empty($call->answered_at)) {
            $payload['answered_at'] = now();
        }
        if ($status === 'completed') {
            $payload['call_duration'] = now()->diffInSeconds($call->call_date);
            $payload['ended_at'] = now();
        }

        $call->update($payload);
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

        $likelyMatches = User::query()
            ->where(function ($query) use ($numbers) {
                foreach ($numbers as $number) {
                    $query->orWhere('phone', 'like', '%' . $number . '%');
                }
            })
            ->get();

        foreach ($likelyMatches as $user) {
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

    private function ucm(): UcmApiService
    {
        return app(UcmApiService::class);
    }
}

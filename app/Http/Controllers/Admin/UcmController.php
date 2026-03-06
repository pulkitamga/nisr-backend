<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCall;
use App\Models\User;
use App\Services\UcmApiService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UcmController extends Controller
{
    private const LIVE_CALLS_CACHE_KEY = 'ucm:live_calls:snapshot:v1';
    private const LIVE_CALLS_REFRESH_LOCK_KEY = 'ucm:live_calls:refresh_lock:v1';

    public function calls(): JsonResponse
    {
        $activeCalls = $this->getCachedLiveCalls();
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

    public function insightsReport(): View
    {
        $snapshotFrom = now()->subDays(89)->startOfDay();
        $snapshotTo = now()->endOfDay();
        $trendStart = now()->copy()->startOfMonth()->subMonths(11);
        $trendEnd = now()->endOfDay();

        $snapshotQuery = CrmCall::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);

        $totalCalls = (clone $snapshotQuery)->count();
        $inboundCalls = (clone $snapshotQuery)->where('direction', 'inbound')->count();
        $outboundCalls = (clone $snapshotQuery)->where('direction', 'outbound')->count();
        $completedCalls = (clone $snapshotQuery)->where('status', 'completed')->count();
        $ongoingCalls = (clone $snapshotQuery)->where('status', 'ongoing')->count();
        $ringingCalls = (clone $snapshotQuery)->where('status', 'ringing')->count();
        $avgDurationSeconds = (float)((clone $snapshotQuery)->where('status', 'completed')->avg('call_duration') ?? 0);
        $totalDurationSeconds = (int)((clone $snapshotQuery)->sum(DB::raw('COALESCE(call_duration, 0)')));
        $answerRate = $totalCalls > 0 ? ($completedCalls / $totalCalls) * 100 : 0;
        $uniqueContacts = (clone $snapshotQuery)
            ->whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');
        $activeAgents = (clone $snapshotQuery)
            ->whereNotNull('agent_id')
            ->distinct()
            ->count('agent_id');

        $trendRows = CrmCall::query()
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_calls")
            ->selectRaw('AVG(COALESCE(call_duration, 0)) as avg_duration')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn($row) => sprintf('%04d-%02d', (int)$row->year, (int)$row->month));

        $trendLabels = [];
        $trendCalls = [];
        $trendCompleted = [];
        $trendAvgDuration = [];
        for ($monthIndex = 0; $monthIndex < 12; $monthIndex++) {
            $monthDate = $trendStart->copy()->addMonths($monthIndex);
            $monthKey = $monthDate->format('Y-m');
            $row = $trendRows->get($monthKey);

            $trendLabels[] = $monthDate->format('M Y');
            $trendCalls[] = (int)($row->total_calls ?? 0);
            $trendCompleted[] = (int)($row->completed_calls ?? 0);
            $trendAvgDuration[] = round((float)($row->avg_duration ?? 0), 1);
        }

        $statusRows = (clone $snapshotQuery)
            ->selectRaw("COALESCE(NULLIF(status, ''), 'unknown') as status_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(status, ''), 'unknown')"))
            ->orderByDesc('total')
            ->get();

        $directionRows = (clone $snapshotQuery)
            ->selectRaw("COALESCE(NULLIF(direction, ''), 'unknown') as direction_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(direction, ''), 'unknown')"))
            ->orderByDesc('total')
            ->get();

        $hourlyRows = CrmCall::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw('HOUR(created_at) as hour_slot, COUNT(*) as total')
            ->groupBy('hour_slot')
            ->orderBy('hour_slot')
            ->get()
            ->pluck('total', 'hour_slot')
            ->all();

        $hourlyLabels = [];
        $hourlyCounts = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyLabels[] = sprintf('%02d:00', $hour);
            $hourlyCounts[] = (int)($hourlyRows[$hour] ?? 0);
        }

        $topAgents = CrmCall::query()
            ->leftJoin('admins', 'admins.id', '=', 'crm_calls.agent_id')
            ->whereBetween('crm_calls.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw('crm_calls.agent_id')
            ->selectRaw("COALESCE(admins.name, 'Unassigned') as agent_name")
            ->selectRaw('COUNT(*) as calls_count')
            ->selectRaw('SUM(COALESCE(crm_calls.call_duration, 0)) as total_duration')
            ->selectRaw('AVG(COALESCE(crm_calls.call_duration, 0)) as avg_duration')
            ->groupBy('crm_calls.agent_id', 'admins.name')
            ->orderByDesc('calls_count')
            ->limit(8)
            ->get();

        $kpi = [
            'total_calls' => $totalCalls,
            'inbound_calls' => $inboundCalls,
            'outbound_calls' => $outboundCalls,
            'completed_calls' => $completedCalls,
            'ongoing_calls' => $ongoingCalls,
            'ringing_calls' => $ringingCalls,
            'answer_rate' => $answerRate,
            'avg_duration_seconds' => $avgDurationSeconds,
            'total_duration_seconds' => $totalDurationSeconds,
            'unique_contacts' => $uniqueContacts,
            'active_agents' => $activeAgents,
        ];

        $trendChartData = [
            'labels' => $trendLabels,
            'calls' => $trendCalls,
            'completed' => $trendCompleted,
            'avg_duration' => $trendAvgDuration,
        ];

        $statusChartData = [
            'labels' => $statusRows->pluck('status_name')->map(fn($value) => ucwords(str_replace('_', ' ', (string)$value)))->values()->all(),
            'counts' => $statusRows->pluck('total')->map(fn($value) => (int)$value)->values()->all(),
        ];

        $directionChartData = [
            'labels' => $directionRows->pluck('direction_name')->map(fn($value) => ucwords(str_replace('_', ' ', (string)$value)))->values()->all(),
            'counts' => $directionRows->pluck('total')->map(fn($value) => (int)$value)->values()->all(),
        ];

        $hourlyChartData = [
            'labels' => $hourlyLabels,
            'counts' => $hourlyCounts,
        ];

        $insights = $this->buildVoipInsights(
            kpi: $kpi,
            trendLabels: $trendLabels,
            trendCalls: $trendCalls,
            trendCompleted: $trendCompleted,
            hourlyLabels: $hourlyLabels,
            hourlyCounts: $hourlyCounts
        );

        return view('admin-views.crm.reports.voip', compact(
            'kpi',
            'trendChartData',
            'statusChartData',
            'directionChartData',
            'hourlyChartData',
            'topAgents',
            'insights',
            'snapshotFrom',
            'snapshotTo'
        ));
    }

    private function buildVoipInsights(
        array $kpi,
        array $trendLabels,
        array $trendCalls,
        array $trendCompleted,
        array $hourlyLabels,
        array $hourlyCounts
    ): array {
        if (($kpi['total_calls'] ?? 0) === 0) {
            return ['No VOIP calls were found in the last 90 days.'];
        }

        $insights = [];
        $insights[] = 'Answer rate is ' . number_format((float)$kpi['answer_rate'], 1) . '% with ' . number_format((int)$kpi['completed_calls']) . ' completed calls.';
        $insights[] = 'Inbound vs outbound split is ' . number_format((int)$kpi['inbound_calls']) . ' / ' . number_format((int)$kpi['outbound_calls']) . '.';
        $insights[] = 'Average handled call duration is ' . $this->formatDurationSeconds((float)$kpi['avg_duration_seconds']) . ', with total talk time of ' . $this->formatDurationSeconds((float)$kpi['total_duration_seconds']) . '.';

        $maxTrendCalls = max($trendCalls);
        if ($maxTrendCalls > 0) {
            $peakMonthIndex = array_search($maxTrendCalls, $trendCalls, true);
            if ($peakMonthIndex !== false && isset($trendLabels[$peakMonthIndex])) {
                $peakCompleted = (int)($trendCompleted[$peakMonthIndex] ?? 0);
                $insights[] = 'Peak call volume was in ' . $trendLabels[$peakMonthIndex] . ' with ' . $maxTrendCalls . ' calls (' . $peakCompleted . ' completed).';
            }
        }

        $maxHourly = max($hourlyCounts);
        if ($maxHourly > 0) {
            $peakHourIndex = array_search($maxHourly, $hourlyCounts, true);
            if ($peakHourIndex !== false && isset($hourlyLabels[$peakHourIndex])) {
                $insights[] = 'Peak call hour is around ' . $hourlyLabels[$peakHourIndex] . ' with ' . $maxHourly . ' calls.';
            }
        }

        return $insights;
    }

    private function formatDurationSeconds(float $seconds): string
    {
        $seconds = max(0, (int)round($seconds));
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }

    private function getCachedLiveCalls(): array
    {
        $cached = Cache::get(self::LIVE_CALLS_CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $lock = Cache::lock(self::LIVE_CALLS_REFRESH_LOCK_KEY, 1);
        if (!$lock->get()) {
            return is_array($cached) ? $cached : [];
        }

        try {
            $fresh = $this->fetchLiveCallsFromUcm();
            Cache::put(self::LIVE_CALLS_CACHE_KEY, $fresh, now()->addSeconds(5));
            return $fresh;
        } finally {
            $lock->release();
        }
    }

    private function fetchLiveCallsFromUcm(): array
    {
        $ucm = $this->ucm();
        if (!$ucm->isAvailable()) {
            $fallback = Cache::get(self::LIVE_CALLS_CACHE_KEY, []);
            return is_array($fallback) ? $fallback : [];
        }

        try {
            $activeCalls = $ucm->getLiveCalls();
            return is_array($activeCalls) ? $activeCalls : [];
        } catch (Throwable $exception) {
            Log::warning('UCM live calls fetch failed; using cached snapshot', [
                'error' => $exception->getMessage(),
            ]);
            $fallback = Cache::get(self::LIVE_CALLS_CACHE_KEY, []);
            return is_array($fallback) ? $fallback : [];
        }
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

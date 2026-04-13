<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UcmWebhookController;
use App\Models\CrmCall;
use App\Models\User;
use App\Services\UcmApiService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ReportPdfService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UcmController extends Controller
{
    private const LIVE_CALLS_CACHE_KEY = 'ucm:alive_calls:snapshot:v2'; // Changed key to force refresh
    private const LIVE_CALLS_REFRESH_LOCK_KEY = 'ucm:alive_calls:refresh_lock:v2';
    private const WEBHOOK_ALIVE_THRESHOLD_SECONDS = 30; // Consider webhooks alive if received within 30s
    private const WEBHOOK_CALL_LOOKBACK_SECONDS = 180;
    private const POLLING_CACHE_SECONDS = 10; // Cache for 10s when polling
    private const WEBHOOK_CACHE_SECONDS = 3; // Cache for 3s when webhooks are active

    public function calls(): JsonResponse
    {
        $activeCalls = $this->getCachedLiveCalls();
        $adminUser = auth('admin')->user();
        $agentId = (int)($adminUser->id ?? 0);
        $employeeNumbers = $this->resolveAdminCallNumbers($adminUser);

        $calls = collect($activeCalls)->map(function (array $call) use ($employeeNumbers, $agentId) {
            $caller = $this->normalizeDigits((string)($call['caller'] ?? ''));
            $callee = $this->normalizeDigits((string)($call['callee'] ?? ''));
            $callId = (string)($call['call_id'] ?? '');
            $channel = (string)($call['channel'] ?? '');

            $contact = $this->resolveContact($caller, $callee);
            $isMine = empty($employeeNumbers)
                ? true
                : $this->matchesAnyAdminNumber($employeeNumbers, [$caller, $callee]);

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

    public function insightsReport(Request $request): View|BinaryFileResponse|Response
    {
        [$snapshotFrom, $snapshotTo] = $this->resolveDateRange($request);
        // Calculate the dynamic range label
        // Format date range for display
        $rangeLabel =
            $snapshotFrom->locale(app()->getLocale())->translatedFormat('d M Y')
            . ' - ' .
            $snapshotTo->locale(app()->getLocale())->translatedFormat('d M Y');
        $resolvedAgentSql = $this->resolvedAgentIdSql('crm_calls');

        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $snapshotFrom->toDateString(),
            'to' => $snapshotTo->toDateString(),
            'direction' => (string)$request->input('direction', ''),
            'status' => (string)$request->input('status', ''),
            'agent_id' => (int)$request->input('agent_id', 0),
        ];

        $trendGrouping = $this->resolveTrendGrouping($snapshotFrom, $snapshotTo);
        $periodKeys = $this->buildPeriodKeys($snapshotFrom, $snapshotTo, $trendGrouping['unit']);

        $snapshotQuery = CrmCall::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        $this->applyVoipFilters($snapshotQuery, $filters, 'crm_calls');

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
        $activeAgents = (int)((clone $snapshotQuery)
            ->selectRaw("COUNT(DISTINCT {$resolvedAgentSql}) as total")
            ->value('total') ?? 0);

        $trendQuery = CrmCall::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw($trendGrouping['select'] . ' as period_key')
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_calls")
            ->selectRaw('AVG(COALESCE(call_duration, 0)) as avg_duration');
        $this->applyVoipFilters($trendQuery, $filters, 'crm_calls');

        $trendRows = $trendQuery
            ->selectRaw($trendGrouping['select'] . ' as period_key')
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $trendLabels = [];
        $trendCalls = [];
        $trendCompleted = [];
        $trendAvgDuration = [];
        foreach ($periodKeys as $periodKey) {
            $row = $trendRows->get($periodKey);
            $trendLabels[] = $this->formatPeriodLabel($periodKey, $trendGrouping['unit']);
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

        $hourlyRows = (clone $snapshotQuery)
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
            ->whereBetween('crm_calls.created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['direction'] !== '', fn($query) => $query->where('crm_calls.direction', $filters['direction']))
            ->when($filters['status'] !== '', fn($query) => $query->where('crm_calls.status', $filters['status']))
            ->when($filters['agent_id'] > 0, fn($query) => $query->whereRaw("{$resolvedAgentSql} = ?", [$filters['agent_id']]))
            ->selectRaw("{$resolvedAgentSql} as agent_id")
            ->selectRaw("COALESCE((SELECT ad.name FROM admins ad WHERE ad.id = {$resolvedAgentSql} LIMIT 1), 'Unassigned') as agent_name")
            ->selectRaw('COUNT(*) as calls_count')
            ->selectRaw('SUM(COALESCE(crm_calls.call_duration, 0)) as total_duration')
            ->selectRaw('AVG(COALESCE(crm_calls.call_duration, 0)) as avg_duration')
            ->groupBy(DB::raw($resolvedAgentSql), DB::raw("COALESCE((SELECT ad.name FROM admins ad WHERE ad.id = {$resolvedAgentSql} LIMIT 1), 'Unassigned')"))
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

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {
            $rows = $topAgents->map(function ($agent) {
                return [
                    (string)$agent->agent_name,
                    (int)$agent->calls_count,
                    round(((float)$agent->total_duration) / 60, 1),
                    round(((float)$agent->avg_duration) / 60, 1),
                ];
            })->values()->all();
            $currentLocale = session('local') ?? session('locale') ?? app()->getLocale();
            return Excel::download(new class($rows, $currentLocale) implements FromArray, WithHeadings, WithStyles {
                public function __construct(
                    private readonly array $rows,
                    private readonly string $locale
                ) {
                    app()->setLocale($this->locale);
                }
                public function array(): array
                {
                    return $this->rows;
                }
                public function headings(): array
                {
                    return [
                        translate('agent'),
                        translate('calls'),
                        translate('total_duration'),
                        translate('avg_duration'),
                    ];
                }
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
                {
                    return [
                        // Row 1: Green Header with White Bold Text
                        1 => [
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF239E92'],
                            ],
                        ],
                    ];
                }
            }, 'ucm-insights-report.xlsx');
        }

        if ($download === 'pdf') {

            $isRtl = \App\Support\LocalizedExport::isRtl();

            // ✅ Get chart images from request
            $trendChart = $request->input('trend_chart');
            $statusChart = $request->input('status_chart');
            $directionChart = $request->input('direction_chart');

            return app(ReportPdfService::class)->download(
                view: 'admin-views.crm.reports.voip-pdf',
                data: array_merge(
                    compact(
                        'kpi',
                        'topAgents',
                        'filters',
                        'snapshotFrom',
                        'snapshotTo',
                        'isRtl',
                        'trendChart',
                        'statusChart',
                        'directionChart'
                    ),
                    [
                        'report_title' => translate('voip_insights_report')
                    ]
                ),
                fileName: 'ucm-insights-report.pdf'
            );
        }

        $filterAgents = CrmCall::query()
            ->whereBetween('crm_calls.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw("{$resolvedAgentSql} as agent_id, COALESCE((SELECT ad.name FROM admins ad WHERE ad.id = {$resolvedAgentSql} LIMIT 1), 'Unassigned') as agent_name")
            ->whereRaw("{$resolvedAgentSql} IS NOT NULL")
            ->groupBy(DB::raw($resolvedAgentSql), DB::raw("COALESCE((SELECT ad.name FROM admins ad WHERE ad.id = {$resolvedAgentSql} LIMIT 1), 'Unassigned')"))
            ->orderBy('agent_name')
            ->get();

        return view('admin-views.crm.reports.voip', compact(
            'kpi',
            'trendChartData',
            'statusChartData',
            'directionChartData',
            'hourlyChartData',
            'topAgents',
            'insights',
            'snapshotFrom',
            'snapshotTo',
            'filters',
            'filterAgents',
            'rangeLabel'
        ));
    }

    private function resolveDateRange(Request $request): array
    {
        $dateType = (string)$request->input('date_type', 'this_year');
        $from = $request->input('from');
        $to = $request->input('to');

        switch ($dateType) {
            case 'this_month':
                $fromDate = now()->startOfMonth()->startOfDay();
                $toDate = now()->endOfMonth()->endOfDay();
                break;
            case 'this_week':
                $fromDate = now()->startOfWeek()->startOfDay();
                $toDate = now()->endOfWeek()->endOfDay();
                break;
            case 'today':
                $fromDate = now()->startOfDay();
                $toDate = now()->endOfDay();
                break;
            case 'custom_date':
                $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(29)->startOfDay();
                $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
                break;
            case 'this_year':
            default:
                $fromDate = now()->startOfYear()->startOfDay();
                $toDate = now()->endOfYear()->endOfDay();
                break;
        }

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate, $toDate];
    }

    private function applyVoipFilters(
        Builder|\Illuminate\Database\Eloquent\Builder $query,
        array $filters,
        string $callAlias = 'crm_calls'
    ): void {
        if (($filters['direction'] ?? '') !== '') {
            $query->where('direction', $filters['direction']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        if (($filters['agent_id'] ?? 0) > 0) {
            $query->whereRaw($this->resolvedAgentIdSql($callAlias) . ' = ?', [(int)$filters['agent_id']]);
        }
    }

    private function resolvedAgentIdSql(string $callAlias = 'crm_calls'): string
    {
        $srcSql = $this->normalizedDigitsSql("{$callAlias}.src_number");
        $dstSql = $this->normalizedDigitsSql("{$callAlias}.dst_number");
        $srcAgentSql = $this->matchAdminIdByNumberSql($srcSql);
        $dstAgentSql = $this->matchAdminIdByNumberSql($dstSql);

        return "COALESCE(CASE"
            . " WHEN {$callAlias}.direction = 'outbound' THEN {$srcAgentSql}"
            . " WHEN {$callAlias}.direction = 'inbound' THEN {$dstAgentSql}"
            . " ELSE COALESCE({$dstAgentSql}, {$srcAgentSql}) END,"
            . " {$callAlias}.agent_id,"
            . " COALESCE({$dstAgentSql}, {$srcAgentSql}))";
    }

    private function matchAdminIdByNumberSql(string $numberSql): string
    {
        $adminPhoneSql = $this->normalizedDigitsSql('a.phone');

        return "(SELECT a.id FROM admins a"
            . " WHERE a.status = 1"
            . " AND {$adminPhoneSql} <> ''"
            . " AND ("
            . " {$adminPhoneSql} = {$numberSql}"
            . " OR {$numberSql} LIKE CONCAT('%', {$adminPhoneSql})"
            . " OR {$adminPhoneSql} LIKE CONCAT('%', {$numberSql})"
            . " )"
            . " ORDER BY CHAR_LENGTH({$adminPhoneSql}) DESC, a.id ASC"
            . " LIMIT 1)";
    }

    private function normalizedDigitsSql(string $columnSql): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$columnSql}, ''), '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), '.', ''), '/', '')";
    }

    private function resolveTrendGrouping(Carbon $fromDate, Carbon $toDate): array
    {
        $days = $fromDate->diffInDays($toDate);
        if ($days <= 31) {
            return ['unit' => 'day', 'select' => 'DATE(created_at)'];
        }
        if ($days <= 180) {
            return ['unit' => 'week', 'select' => "DATE_FORMAT(created_at, '%x-W%v')"];
        }
        return ['unit' => 'month', 'select' => "DATE_FORMAT(created_at, '%Y-%m')"];
    }

    private function buildPeriodKeys(Carbon $fromDate, Carbon $toDate, string $unit): array
    {
        $keys = [];
        $cursor = $fromDate->copy();

        if ($unit === 'day') {
            while ($cursor->lte($toDate)) {
                $keys[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
            return $keys;
        }

        if ($unit === 'week') {
            $cursor = $fromDate->copy()->startOfWeek();
            $limit = $toDate->copy()->endOfWeek();
            while ($cursor->lte($limit)) {
                $keys[] = $cursor->format('o-\WW');
                $cursor->addWeek();
            }
            return $keys;
        }

        $cursor = $fromDate->copy()->startOfMonth();
        $limit = $toDate->copy()->endOfMonth();
        while ($cursor->lte($limit)) {
            $keys[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $keys;
    }

    private function formatPeriodLabel(string $periodKey, string $unit): string
    {
        if ($unit === 'day') {
            return Carbon::parse($periodKey)->format('M d');
        }
        if ($unit === 'week') {
            [$year, $week] = explode('-W', $periodKey);
            return 'W' . $week . ' ' . $year;
        }
        return Carbon::createFromFormat('Y-m', $periodKey)->format('M Y');
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
            return [translate('no_voip_calls_found_in_last_90_days')];
        }

        $insights = [];
        $insights[] = strtr(translate('voip_insight_answer_rate'), [
            ':answer_rate' => number_format((float)$kpi['answer_rate'], 1),
            ':completed_calls' => number_format((int)$kpi['completed_calls']),
        ]);
        $insights[] = strtr(translate('voip_insight_direction_split'), [
            ':inbound_calls' => number_format((int)$kpi['inbound_calls']),
            ':outbound_calls' => number_format((int)$kpi['outbound_calls']),
        ]);
        $insights[] = strtr(translate('voip_insight_durations'), [
            ':avg_duration' => $this->formatDurationSeconds((float)$kpi['avg_duration_seconds']),
            ':total_duration' => $this->formatDurationSeconds((float)$kpi['total_duration_seconds']),
        ]);

        $maxTrendCalls = max($trendCalls);
        if ($maxTrendCalls > 0) {
            $peakMonthIndex = array_search($maxTrendCalls, $trendCalls, true);
            if ($peakMonthIndex !== false && isset($trendLabels[$peakMonthIndex])) {
                $peakCompleted = (int)($trendCompleted[$peakMonthIndex] ?? 0);
                $insights[] = strtr(translate('voip_insight_peak_month'), [
                    ':period' => $trendLabels[$peakMonthIndex],
                    ':calls' => (string)$maxTrendCalls,
                    ':completed' => (string)$peakCompleted,
                ]);
            }
        }

        $maxHourly = max($hourlyCounts);
        if ($maxHourly > 0) {
            $peakHourIndex = array_search($maxHourly, $hourlyCounts, true);
            if ($peakHourIndex !== false && isset($hourlyLabels[$peakHourIndex])) {
                $insights[] = strtr(translate('voip_insight_peak_hour'), [
                    ':hour' => $hourlyLabels[$peakHourIndex],
                    ':calls' => (string)$maxHourly,
                ]);
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
            if (UcmWebhookController::isWebhookAlive(self::WEBHOOK_ALIVE_THRESHOLD_SECONDS)) {
                $fresh = $this->getRecentWebhookCalls();
                $cacheTtl = self::WEBHOOK_CACHE_SECONDS;
            } else {
                $fresh = $this->fetchLiveCallsFromUcm();
                $cacheTtl = self::POLLING_CACHE_SECONDS;
            }
            Cache::put(self::LIVE_CALLS_CACHE_KEY, $fresh, now()->addSeconds($cacheTtl));
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

        if ($ucm->isInTransportFailureCooldown()) {
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

    private function getRecentWebhookCalls(): array
    {
        $cutoff = now()->subSeconds(self::WEBHOOK_CALL_LOOKBACK_SECONDS);

        return CrmCall::query()
            ->whereIn('status', ['ringing', 'ongoing'])
            ->where(function ($query) use ($cutoff) {
                $query->where('updated_at', '>=', $cutoff)
                    ->orWhere('started_at', '>=', $cutoff);
            })
            ->orderByDesc('updated_at')
            ->limit(25)
            ->get()
            ->map(function (CrmCall $call): array {
                return [
                    'call_id' => (string)$call->call_id,
                    'channel' => (string)($call->ucm_channel ?? ''),
                    'peer_channel' => (string)($call->ucm_peer_channel ?? ''),
                    'caller' => (string)($call->src_number ?? ''),
                    'callee' => (string)($call->dst_number ?? ''),
                    'status' => (string)($call->status ?? 'ringing'),
                    'direction' => (string)($call->direction ?? 'inbound'),
                    'started_at' => optional($call->started_at)->toIso8601String(),
                    'ucm_uniqueid' => (string)($call->ucm_uniqueid ?? ''),
                    'ucm_bridge_id' => (string)($call->ucm_bridge_id ?? ''),
                    'raw' => $call->raw_payload,
                ];
            })
            ->values()
            ->all();
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
            $this->updateCallStatus($validated['call_id'] ?? null, 'ongoing', (int)auth('admin')->id());
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

    private function updateCallStatus(?string $callId, string $status, ?int $agentId = null): void
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
        if ($status === 'ongoing' && $agentId) {
            $payload['agent_id'] = $agentId;
        }
        if ($status === 'completed') {
            $payload['call_duration'] = now()->diffInSeconds($call->call_date);
            $payload['ended_at'] = now();
        }

        $call->update($payload);
    }

    private function resolveAdminCallNumbers(mixed $adminUser): array
    {
        if (!$adminUser) {
            return [];
        }

        return array_values(array_filter(array_unique([
            $this->normalizeDigits((string)($adminUser->extension ?? '')),
            $this->normalizeDigits((string)($adminUser->phone ?? '')),
        ])));
    }

    private function matchesAnyAdminNumber(array $adminNumbers, array $callNumbers): bool
    {
        $normalizedCallNumbers = array_values(array_filter(array_unique(array_map(
            fn(string $number): string => $this->normalizeDigits($number),
            $callNumbers
        ))));

        foreach ($adminNumbers as $adminNumber) {
            foreach ($normalizedCallNumbers as $callNumber) {
                if ($adminNumber === '' || $callNumber === '') {
                    continue;
                }

                if (
                    $adminNumber === $callNumber
                    || str_ends_with($callNumber, $adminNumber)
                    || str_ends_with($adminNumber, $callNumber)
                ) {
                    return true;
                }
            }
        }

        return false;
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

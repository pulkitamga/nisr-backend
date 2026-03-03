<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\InboxMessage;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\CalendarTodo;
use App\Models\InboxTask;
use App\Models\InboxCall;
use App\Models\LeadTask;
use App\Models\LeadCall;
use App\Models\DealTask;
use App\Models\DealCall;
use App\Models\Departments;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin-views.crm.calendar');
    }

    public function events(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->toDateString() : now()->startOfMonth()->toDateString();
        $end = $request->end ? Carbon::parse($request->end)->toDateString() : now()->endOfMonth()->toDateString();

        $user = Auth::guard('admin')->user();
        $userId = $user->id;

        // Department employees
        $employeeIds = [];
        $departmentId = null;
        if (!$this->isSuperAdmin($user)) {
            $department = Departments::where('head_id', $userId)->first();

            if ($department) {
                $departmentId = $department->id;
                $employeeIds = \App\Models\Admin::where('department_id', $department->id)->pluck('id')->toArray();
            }
        }


        $events = [];

        // Inbox
        $events = array_merge($events, $this->fetchEvents(
            InboxMessage::class,
            [InboxTask::class, InboxCall::class],
            ['Inbox Task', 'Inbox Call'],
            ['due_date', 'from'],
            ['name', 'title'],
            ['admin.crm.massage.show', 'admin.crm.massage.show'],
            $userId,
            $employeeIds,
            false,
            $start,
            $end,
            $departmentId
        ));

        // Lead
        $events = array_merge($events, $this->fetchEvents(
            Lead::class,
            [LeadTask::class, LeadCall::class],
            ['Lead Task', 'Lead Call'],
            ['due_date', 'from'],
            ['name', 'title'],
            ['admin.crm.lead.show', 'admin.crm.lead.show'],
            $userId,
            $employeeIds,
            false,
            $start,
            $end,
            $departmentId
        ));
        $events = array_merge($events, $this->fetchEvents(
            Deal::class,
            [DealTask::class, DealCall::class],
            ['Deal Task', 'Deal Call'],
            ['due_date', 'from'],
            ['name', 'title'],
            ['admin.crm.deals.retail.view', 'admin.crm.deals.retail.view'], // default retail
            $userId,
            $employeeIds,
            true,
            $start,
            $end,
            $departmentId
        ));

        $todos = CalendarTodo::where(function ($q) use ($user) {
            $q->where('employee_id', $user->id);
            if ($this->isSuperAdmin($user)) {
                $q->orWhereNotNull('employee_id');
            }
        })->get();
        foreach ($todos as $todo) {
            $events[] = [
                'title' => 'To-Do: ' . Str::limit($todo->note, 50),
                'start' => $todo->date,
                'color' => '#dc3545',
                'description' => $todo->note,
                'type' => 'To-Do',
                'employee' => $todo->employee->name ?? '',
            ];
        }
        return response()->json($events);
    }

    private function fetchEvents($mainModel, $subModels, $prefixes, $dateFields, $titleFields, $routeNames, $userId, $employeeIds = [], $isDeal = false, $start = null, $end = null, $departmentId = null)
    {
        $relationMeta = [];
        foreach ($subModels as $i => $subModel) {
            $relation = $this->getRelationName($subModel);
            if (!$relation) {
                continue;
            }
            $relationMeta[] = [
                'relation' => $relation,
                'prefix' => $prefixes[$i],
                'dateField' => $dateFields[$i],
                'titleField' => $titleFields[$i],
                'routeName' => $routeNames[$i],
            ];
        }

        if (empty($relationMeta)) {
            return [];
        }

        $mainQuery = $mainModel::query();
        if (!$this->isSuperAdmin(Auth::guard('admin')->user())) {
            $mainQuery->where(function ($q) use ($userId, $employeeIds, $departmentId) {
                $q->where('employee_id', $userId)
                    ->orWhere('owner_id', $userId);

                if ($departmentId) {
                    $q->orWhere('department_id', $departmentId);
                }

                if (!empty($employeeIds)) {
                    $q->orWhereIn('employee_id', $employeeIds);
                }
            });
        }

        if ($start && $end) {
            $from = $start . ' 00:00:00';
            $to = $end . ' 23:59:59';

            $mainQuery->where(function ($q) use ($relationMeta, $from, $to) {
                foreach ($relationMeta as $idx => $meta) {
                    $method = $idx === 0 ? 'whereHas' : 'orWhereHas';
                    $q->{$method}($meta['relation'], function ($subQ) use ($meta, $from, $to) {
                        $subQ->whereBetween($meta['dateField'], [$from, $to]);
                    });
                }
            });
        }

        $with = [];
        foreach ($relationMeta as $meta) {
            $with[$meta['relation']] = function ($subQ) use ($meta, $start, $end) {
                if ($start && $end) {
                    $subQ->whereBetween(
                        $meta['dateField'],
                        [$start . ' 00:00:00', $end . ' 23:59:59']
                    );
                }
                $subQ->with('employee');
            };
        }
        $mainItems = $mainQuery->with($with)->get();

        $events = [];
        foreach ($mainItems as $main) {
            foreach ($relationMeta as $meta) {
                $subItems = $main->{$meta['relation']} ?? collect();

                foreach ($subItems as $item) {
                    $dateField = $meta['dateField'];
                    if (empty($item->{$dateField})) {
                        continue;
                    }

                    $prefix = $meta['prefix'];
                    $routeName = $meta['routeName'];

                    if ($isDeal) {
                        $isWholesaleDeal = ($main->related_party_type ?? null) === 'company';
                        $typePrefix = $isWholesaleDeal ? 'WS' : 'RT';
                        $prefix = "[$typePrefix] " . $prefix;

                        $routeName = $isWholesaleDeal
                            ? 'admin.crm.deals.wholesale.view'
                            : 'admin.crm.deals.retail.view';
                    }

                    $events[] = [
                        'title' => $prefix . ': ' . Str::limit((string)($item->{$meta['titleField']} ?? ''), 40),
                        'start' => $item->{$dateField} ? Carbon::parse($item->{$dateField})->toIso8601String() : null,
                        'color' => $this->getColor($meta['prefix']),
                        'url' => route($routeName, $main->id),
                        'description' => (string)($item->{$meta['titleField']} ?? ''),
                        'employee' => $item->employee->name ?? null,
                        'type' => $prefix,
                        'deal_type' => ($main->related_party_type ?? null) === 'company' ? 'wholesale' : 'retail',
                    ];
                }
            }
        }
        return $events;
    }


    private function getRelationName($subModel)
    {
        $mapping = [
            InboxTask::class => 'tasks',
            InboxCall::class => 'calls',

            LeadTask::class => 'tasks',
            LeadCall::class => 'calls',

            DealTask::class => 'tasks',
            DealCall::class => 'calls',
        ];
        return $mapping[$subModel] ?? null;
    }
    private function getColor($prefix)
    {
        $colors = [
            'Inbox Task' => 'blue',
            'Inbox Call' => 'green',
            'Lead Task' => 'blue',
            'Lead Call' => 'green',
            'Deal Task' => 'blue',
            'Deal Call' => 'green',
        ];

        return $colors[$prefix] ?? 'gray';
    }

    public function addTodo(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'note' => 'required|string|max:1000',
        ]);

        $todo = CalendarTodo::create([
            'employee_id' => Auth::guard('admin')->id(),
            'date' => $request->date,
            'note' => $request->note,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'To-do added successfully!'
        ]);
    }

    private function isSuperAdmin($admin): bool
    {
        return (int)($admin?->admin_role_id ?? 0) === 1;
    }
}

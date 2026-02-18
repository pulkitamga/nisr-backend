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
use App\Models\InboxNote;
use App\Models\InboxActivities;
use App\Models\LeadTask;
use App\Models\LeadCall;
use App\Models\LeadNote;
use App\Models\LeadActivity;
use App\Models\DealTask;
use App\Models\DealCall;
use App\Models\DealNote;
use App\Models\DealActivity;
use illuminate\Support\Facades\Log;
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
        if ($user->id != 1) {
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
            [InboxTask::class, InboxCall::class, InboxNote::class, InboxActivities::class],
            ['Inbox Task', 'Inbox Call', 'Inbox Note', 'Inbox Activity'],
            ['due_date', 'from', 'noted_at', 'note_date'],
            ['name', 'title', 'note', 'title'],
            ['admin.crm.massage.show', 'admin.crm.massage.show', 'admin.crm.massage.show', 'admin.crm.massage.show'],
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
            [LeadTask::class, LeadCall::class, LeadNote::class, LeadActivity::class],
            ['Lead Task', 'Lead Call', 'Lead Note', 'Lead Activity'],
            ['due_date', 'from', 'noted_at', 'note_date'],
            ['name', 'title', 'note', 'title'],
            ['admin.crm.lead.show', 'admin.crm.lead.show', 'admin.crm.lead.show', 'admin.crm.lead.show'],
            $userId,
            $employeeIds,
            false,
            $start,
            $end,
            $departmentId
        ));
        $events = array_merge($events, $this->fetchEvents(
            Deal::class,
            [DealTask::class, DealCall::class, DealNote::class, DealActivity::class],
            ['Deal Task', 'Deal Call', 'Deal Note', 'Deal Activity'],
            ['due_date', 'from', 'noted_at', 'note_date'],
            ['name', 'title', 'note', 'title'],
            ['admin.crm.deals.retail.view', 'admin.crm.deals.retail.view', 'admin.crm.deals.retail.view', 'admin.crm.deals.retail.view'], // default retail
            $userId,
            $employeeIds,
            true,
            $start,
            $end,
            $departmentId
        ));

        $todos = CalendarTodo::where(function ($q) use ($user) {
            $q->where('employee_id', $user->id);
            if ($user->id == 1) {
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
        if ($userId == 1) {
            $mainItems = $mainModel::all();
        } else {
            $mainItems = $mainModel::where(function ($q) use ($userId, $employeeIds, $departmentId) {
                $q->where('employee_id', $userId)
                    ->orWhere('owner_id', $userId);

                if ($departmentId) {
                    $q->orWhere('department_id', $departmentId);
                }

                if (!empty($employeeIds)) {
                    $q->orWhereIn('employee_id', $employeeIds);
                }
            })->get();
        }

        $events = [];
        foreach ($mainItems as $main) {
            foreach ($subModels as $i => $subModel) {
                $dateField = $dateFields[$i];
                $titleField = $titleFields[$i];
                $fk = $this->getForeignKey($main, $subModel);

                if (!$fk) continue;

                $subQuery = $subModel::where($fk, $main->id);
                if ($start && $end) {
                    $subQuery->whereBetween($dateField, [$start . ' 00:00:00', $end . ' 23:59:59']);
                }
                $subItems = $subQuery->get();

                foreach ($subItems as $item) {
                    if (empty($item->{$dateField})) {
                        continue;
                    }

                    $prefix = $prefixes[$i];
                    $routeName = $routeNames[$i];

                    if ($isDeal && $main->deal_type) {
                        $typePrefix = $main->deal_type === 'wholesale' ? 'WS' : 'RT';
                        $prefix = "[$typePrefix] " . $prefix;

                        // Correct Route
                        $routeName = $main->deal_type === 'wholesale'
                            ? 'admin.crm.deals.wholesale.view'
                            : 'admin.crm.deals.retail.view';
                    }

                    $events[] = [
                        'title' => $prefix . ': ' . Str::limit($item->{$titleField}, 40),
                        'start' => $item->{$dateField} ? Carbon::parse($item->{$dateField})->toIso8601String() : null,
                        'color' => $this->getColor($prefixes[$i], $main->deal_type ?? null),
                        'url' => route($routeName, $main->id),
                        'description' => $item->{$titleField},
                        'employee' => $item->employee->name ?? null,
                        'type' => $prefix,
                        'deal_type' => $main->deal_type ?? 'retail',
                    ];
                }
            }
        }
        return $events;
    }


    private function getForeignKey($mainModel, $subModel)
    {
        $mapping = [
            InboxTask::class => 'massage_id',
            InboxCall::class => 'massage_id',
            InboxNote::class => 'massage_id',
            InboxActivities::class => 'massage_id',

            LeadTask::class => 'lead_id',
            LeadCall::class => 'lead_id',
            LeadNote::class => 'lead_id',
            LeadActivity::class => 'lead_id',

            DealTask::class => 'deal_id',
            DealCall::class => 'deal_id',
            DealNote::class => 'deal_id',
            DealActivity::class => 'deal_id',
        ];
        return $mapping[$subModel] ?? null;
    }
    private function getColor($prefix)
    {
        $colors = [
            'Inbox Task' => 'blue',
            'Inbox Call' => 'green',
            'Inbox Note' => 'purple',
            'Inbox Activity' => 'orange',
            'Lead Task' => 'blue',
            'Lead Call' => 'green',
            'Lead Note' => 'purple',
            'Lead Activity' => 'orange',
            'Deal Task' => 'blue',
            'Deal Call' => 'green',
            'Deal Note' => 'purple',
            'Deal Activity' => 'orange',
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
}

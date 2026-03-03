<?php

namespace App\Http\Controllers\Admin\Crm;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Enums\WebConfigKey;
use App\Traits\PaginatorTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use App\Enums\ViewPaths\Admin\Deals;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Models\SupportTicketDepartmentEmployee;
use App\Models\SupportTicketStatusMaster;
use App\Models\LeadNotification;
use App\Models\CronConfiguration;
use App\Models\CronSenderDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\WholeSalerBusiness;
use App\Models\User;
use App\Models\Admin;
use App\Services\LeadConvertService;
use Illuminate\Support\Facades\DB;
use App\Models\DealActivity;
use App\Models\DealCall;
use App\Models\Order;
use App\Models\DealFile;
use App\Models\DealNote;
use App\Models\DealTask;
use Illuminate\Support\Facades\Auth;
use App\Services\Crm\EscalationService;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use Illuminate\Validation\ValidationException;

class DealController extends BaseController
{

    public function __construct(
        private readonly SupportTicketRepositoryInterface       $supportTicketRepo,
        private readonly SupportTicketConvRepositoryInterface   $supportTicketConvRepo,
        private readonly DepartmentRepositoryInterface          $departmentRepo,
        private readonly AdminRepositoryInterface               $adminRepo,
        private readonly AdminNotificationRepositoryInterface   $notificationRepo,
        private readonly EscalationService                      $escalationService,
    ) {}

    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }
    public function getListView(Request $request)
    {

        $query = Deal::with(['owner', 'relatedParty', 'employee', 'lead'])
            ->where('related_party_type', 'company');
         
        $dataLimit = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);

       if ($request->filled('searchValue')) {
    $search = trim($request->searchValue);

    $query->where(function ($q) use ($search) {

        // User (contact) se search
        $q->orWhereHas('user', function ($sub) use ($search) {
            $sub->where('f_name', 'LIKE', "%{$search}%")
                ->orWhere('l_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%");
        });

        // Company name se search (wholesaler_businesses)
        $q->orWhereExists(function ($exists) use ($search) {
            $exists->select(DB::raw(1))
                   ->from('wholesaler_businesses')
                   ->whereColumn('wholesaler_businesses.id', 'deals.related_party_id')
                   ->where('deals.related_party_type', 'company')
                   ->where('wholesaler_businesses.company_name', 'LIKE', "%{$search}%");
        });
    });
}
        $filterDate = $request->input('filter_date', $request->input('fhilter_date'));
        if (!empty($filterDate)) {
            $dateRange = explode(' - ', $filterDate);
            if (count($dateRange) === 2) {
                $from = date('Y-m-d 00:00:00', strtotime($dateRange[0]));
                $to   = date('Y-m-d 23:59:59', strtotime($dateRange[1]));
                $query->whereBetween('created_at', [$from, $to]);
            }
        }
        if ($request->has('status')) {
            if ($request->status === 'all') {
            } else {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'open');
        }

        $perPage = ($request->filled('choose_first') && (int)$request->choose_first > 0)
            ? (int)$request->choose_first
            : (int)$dataLimit;

        $deals = $query->latest()->paginate(perPage: $perPage)->appends($request->all());
        $getDepartment  = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );

        return view(Deals::INDEX[VIEW], compact('deals', 'getDepartment', 'employees'));
    }



    public function getRetailView(Request $request)
    {
        $query = Deal::with(['owner', 'relatedParty', 'employee', 'lead', 'order'])
            ->where('related_party_type', 'contact');


        $dataLimit = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);

        if ($request->filled('searchValue')) {
            $search = $request->searchValue;

            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($sub) use ($search) {
                    $sub->where('f_name', 'LIKE', "%{$search}%")
                        ->orWhere('l_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                })->orWhereHas('lead.inboxMessages', function ($subQ) use ($search) {
                    $subQ->where('sender_name', 'like', "%{$search}%")
                        ->orWhere('sender_email', 'like', "%{$search}%")
                        ->orWhere('sender_phone', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            });
        }

        $filterDate = $request->input('filter_date', $request->input('fhilter_date'));
        if (!empty($filterDate)) {
            $dateRange = explode(' - ', $filterDate);
            if (count($dateRange) === 2) {
                $from = date('Y-m-d 00:00:00', strtotime($dateRange[0]));
                $to   = date('Y-m-d 23:59:59', strtotime($dateRange[1]));
                $query->whereBetween('created_at', [$from, $to]);
            }
        }
        if ($request->has('status')) {
            if ($request->status === 'all') {
            } else {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'open');
        }

        $perPage = ($request->filled('choose_first') && (int)$request->choose_first > 0)
            ? (int)$request->choose_first
            : (int)$dataLimit;

        $deals = $query->latest()->paginate(perPage: $perPage)->appends($request->all());
        $getDepartment  = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );

        return view(Deals::RETAILER[VIEW], compact('deals', 'getDepartment', 'employees'));
    }

    public function view($id, Request $request)
    {
        if ($request->has('notification_id')) {
            LeadNotification::where('id', $request->notification_id)
                ->where('user_id', auth('admin')->id())
                ->update(['status' => 1]);
        }

        $deal = Deal::with([
            'owner',
            'department',
            'relatedParty',
            'employee',
            'lead',
            'relatedUser',
            'escalations.escalatedBy',
            'activities',
            'notes',
            'tasks',
            'calls',
            'files'
        ])->findOrFail($id);

        return view(Deals::VIEW[VIEW], compact('deal'));
    }

    public function retailView($id, Request $request)
    {
        if ($request->has('notification_id')) {
            LeadNotification::where('id', $request->notification_id)
                ->where('user_id', auth('admin')->id())
                ->update(['status' => 1]);
        }

        $deal = Deal::with([
            'owner',
            'order',
            'department',
            'relatedParty',
            'employee',
            'lead',
            'relatedUser',
            'escalations.escalatedBy',
            'activities',
            'notes',
            'tasks',
            'calls',
            'files'
        ])->findOrFail($id);

        return view(Deals::RETAIL_VIEW[VIEW], compact('deal'));
    }

    public function exportList(Request $request)
    {
        $isRetail = $request->routeIs('admin.crm.deals.retail.export');

        $query = Deal::with(['owner', 'employee', 'user'])
            ->where('related_party_type', $isRetail ? 'contact' : 'company');

        if ($request->filled('searchValue')) {
            $search = trim($request->searchValue);
            $query->where(function ($q) use ($search, $isRetail) {
                $q->orWhereHas('user', function ($sub) use ($search) {
                    $sub->where('f_name', 'LIKE', "%{$search}%")
                        ->orWhere('l_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });

                if (!$isRetail) {
                    $q->orWhereExists(function ($exists) use ($search) {
                        $exists->select(DB::raw(1))
                            ->from('wholesaler_businesses')
                            ->whereColumn('wholesaler_businesses.id', 'deals.related_party_id')
                            ->where('deals.related_party_type', 'company')
                            ->where('wholesaler_businesses.company_name', 'LIKE', "%{$search}%");
                    });
                } else {
                    $q->orWhereHas('lead.inboxMessages', function ($subQ) use ($search) {
                        $subQ->where('sender_name', 'LIKE', "%{$search}%")
                            ->orWhere('sender_email', 'LIKE', "%{$search}%")
                            ->orWhere('sender_phone', 'LIKE', "%{$search}%")
                            ->orWhere('subject', 'LIKE', "%{$search}%")
                            ->orWhere('body', 'LIKE', "%{$search}%");
                    });
                }
            });
        }

        $filterDate = $request->input('filter_date', $request->input('fhilter_date'));
        if (!empty($filterDate)) {
            $dateRange = explode(' - ', $filterDate);
            if (count($dateRange) === 2) {
                $from = date('Y-m-d 00:00:00', strtotime($dateRange[0]));
                $to   = date('Y-m-d 23:59:59', strtotime($dateRange[1]));
                $query->whereBetween('created_at', [$from, $to]);
            }
        }

        if ($request->has('status')) {
            if ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'open');
        }

        $deals = $query->latest()->get();
        $filename = $isRetail ? 'retail-deals.csv' : 'wholesale-deals.csv';

        return response()->streamDownload(function () use ($deals) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Party Type', 'Status', 'Stage', 'Value', 'Owner', 'Employee', 'Created At']);
            foreach ($deals as $deal) {
                fputcsv($handle, [
                    $deal->id,
                    $deal->related_party_type,
                    $deal->status,
                    $deal->stage,
                    $deal->value,
                    $deal->owner?->name,
                    $deal->employee?->name,
                    $deal->created_at,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function disqualify(Request $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'nullable|exists:deals,id',
            'deal_id' => 'nullable|exists:deals,id',
            'ticket_id' => 'nullable|exists:deals,id',
        ]);

        $dealId = $request->message_id ?? $request->deal_id ?? $request->ticket_id;
        $deal = Deal::findOrFail($dealId);
        $authUser = auth('admin')->user();

        if (
            !$this->isSuperAdmin($authUser) &&
            $deal->employee?->id !== $authUser->id &&
            $deal->department?->head_id !== $authUser->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to disqualify this deal.',
            ], 403);
        }

        $deal->status = 'lost';
        $deal->save();

        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'disqualification';
        $activity->title = 'Deal Disqualified';
        $activity->subject = 'Deal disqualified by ' . $authUser->name;
        $activity->note_date = now();
        $activity->employee_id = $authUser->id;
        $activity->details = [
            'status' => 'lost',
            'deal_id' => $deal->id,
        ];
        $activity->save();

        return response()->json([
            'status' => true,
            'message' => 'Deal disqualified successfully!',
        ]);
    }

    public function storeActivity(Request $request, $id): JsonResponse
    {
        $request->validate([
            'activity_type' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'details' => 'nullable',
            'note_date' => 'nullable|date',
        ]);

        $deal = Deal::findOrFail($id);

        $details = $request->input('details');
        if (is_string($details)) {
            $details = ['description' => $details];
        } elseif (!is_array($details)) {
            $details = [];
        }

        $subject = $request->input('subject')
            ?? (isset($details['description']) ? substr((string)$details['description'], 0, 255) : 'Activity logged');

        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = $request->input('activity_type', 'activity');
        $activity->title = $request->input('title', 'Activity Added');
        $activity->subject = $subject;
        $activity->note_date = $request->input('note_date', now());
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = $details;
        $activity->save();

        $activities = $deal->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $activityHtml = view('admin-views.crm.deals.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Activity saved successfully!'),
            'activity_html' => $activityHtml,
        ]);
    }


    public function destroy($id)
    {
        $deal = Deal::findOrFail($id);
        $deal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deal deleted successfully!'
        ]);
    }


    public function requestQuotation($dealId)
    {
        $deal = Deal::findOrFail($dealId);
        $admins = Admin::permission('wholesaler_section.create_quotation')->get();

        foreach ($admins as $admin) {
            LeadNotification::create([
                'user_id'     => $admin->id,
                'related_id'     => $deal->id,
                'title'       => 'Quotation Request',
                'message'     => 'A new quotation request has been made for Deal ID: ' . $deal->id,
                'status'      => 'unread',
                'created_by'  => auth('admin')->id() ?? null,
            ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Quotation request notification sent to eligible admins.'
        ]);
    }

    public function storeNote(Request $request, $id): JsonResponse
    {
        $request->validate([
            'note' => 'required|string',
            'noted_at' => 'required|date',
        ]);

        $deal = Deal::findOrFail($id);

        $note = new DealNote();
        $note->deal_id = $deal->id;
        $note->note = $request->note;
        $note->noted_at = $request->noted_at;
        $note->employee_id = Auth::guard('admin')->id();
        $note->save();

        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'note';
        $activity->title = 'Note Added';
        $activity->subject = substr($request->note, 0, 255); // Truncate for subject
        $activity->note_date = $request->noted_at;
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = ['note_id' => $note->id, 'content' => $request->note];
        $activity->save();

        $notes = $deal->notes()->latest()->get();
        $activities = $deal->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $noteHtml = view('admin-views.crm.deals.partials.note_list', compact('notes'))->render();
        $activityHtml = view('admin-views.crm.deals.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Note saved successfully!'),
            'html' => $noteHtml,
            'activity_html' => $activityHtml,
        ]);
    }

    public function storeTask(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,complete',
        ]);

        $deal = Deal::findOrFail($id);

        $task = new DealTask();
        $task->deal_id = $deal->id;
        $task->name = $request->name;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->status = $request->status;
        $task->employee_id = Auth::guard('admin')->id();
        $task->department_id = $deal->department_id;
        $task->save();

        // Log activity for task
        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'task';
        $activity->title = 'Task Added: ' . $request->name;
        $activity->subject = $request->description ? substr($request->description, 0, 255) : 'Task created';
        $activity->note_date = $request->due_date;
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = [
            'task_id' => $task->id,
            'name' => $request->name,
            'status' => $request->status,
        ];
        $activity->save();

        $tasks = $deal->tasks()->latest()->get();
        $activities = $deal->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $taskHtml = view('admin-views.crm.deals.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.deals.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Task saved successfully!'),
            'html' => $taskHtml,
            'activity_html' => $activityHtml,
        ]);
    }

    public function storeCall(Request $request, $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'from' => 'required|date',
            'to' => 'required|date|after:from',
            'guests' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $deal = Deal::findOrFail($id);

        $call = new DealCall();
        $call->deal_id = $deal->id;
        $call->title = $request->title;
        $call->from = $request->from;
        $call->to = $request->to;
        $call->guests = $request->guests;
        $call->location = $request->location;
        $call->description = $request->description;
        $call->employee_id = Auth::guard('admin')->id();
        $call->department_id = $deal->department_id;
        $call->save();

        // Log activity for call
        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'call';
        $activity->title = 'Call Scheduled: ' . $request->title;
        $activity->subject = $request->description ? substr($request->description, 0, 255) : 'Call scheduled';
        $activity->note_date = $request->from; // Use 'from' date for activity
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = [
            'call_id' => $call->id,
            'title' => $request->title,
            'from' => $request->from,
            'to' => $request->to,
        ];
        $activity->save();

        // Render the updated call and activity lists
        $calls = $deal->calls()->latest()->get();
        $activities = $deal->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $callHtml = view('admin-views.crm.deals.partials.call_list', compact('calls'))->render();
        $activityHtml = view('admin-views.crm.deals.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Call saved successfully!'),
            'html' => $callHtml,
            'activity_html' => $activityHtml,
        ]);
    }

    public function storeFile(Request $request, $id): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        $deal = Deal::findOrFail($id);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads/deal_files', $fileName, 'public');

            $fileModel = new DealFile();
            $fileModel->deal_id = $deal->id;
            $fileModel->file = $filePath;
            $fileModel->employee_id = Auth::guard('admin')->id();
            $fileModel->save();

            $activity = new DealActivity();
            $activity->deal_id = $deal->id;
            $activity->activity_type = 'file';
            $activity->title = 'File Uploaded: ' . $fileName;
            $activity->subject = 'File uploaded for deal';
            $activity->note_date = now();
            $activity->employee_id = Auth::guard('admin')->id();
            $activity->details = [
                'file_id' => $fileModel->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
            ];
            $activity->save();

            $files = $deal->files()->latest()->get();
            $activities = $deal->activities()
                ->orderByRaw('COALESCE(updated_at, created_at) DESC')
                ->get();
            $fileHtml = view('admin-views.crm.deals.partials.file_list', compact('files'))->render();
            $activityHtml = view('admin-views.crm.deals.partials.activity_list', compact('activities'))->render();

            return response()->json([
                'status' => true,
                'message' => translate('File uploaded successfully!'),
                'html' => $fileHtml,
                'activity_html' => $activityHtml,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => translate('File upload failed!'),
        ], 400);
    }


    public function updateTask(Request $request, $id, $task_id): JsonResponse
    {
        $request->validate([
            'task_id' => 'required|exists:deal_tasks,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,complete',
        ]);

        $deal = Deal::findOrFail($id);
        $task = DealTask::findOrFail($task_id);

        if ($task->deal_id !== $deal->id) {
            return response()->json([
                'status' => false,
                'message' => translate('Task does not belong to this deal!'),
            ], 403);
        }

        $task->name = $request->name;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->status = $request->status;
        $task->employee_id = Auth::guard('admin')->id();
        $task->save();

        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'task';
        $activity->title = 'Task Updated: ' . $request->name;
        $activity->subject = $request->description ? substr($request->description, 0, 255) : 'Task updated';
        $activity->note_date = $request->due_date;
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = [
            'task_id' => $task->id,
            'name' => $request->name,
            'status' => $request->status,
        ];
        $activity->save();

        $tasks = $deal->tasks()->latest()->get();
        $activities = $deal->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $taskHtml = view('admin-views.crm.deals.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.deals.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Task updated successfully!'),
            'html' => $taskHtml,
            'activity_html' => $activityHtml,
        ]);
    }

    public function completeTask(Request $request, $id, $task_id): JsonResponse
    {
        $deal = Deal::findOrFail($id);
        $task = DealTask::findOrFail($task_id);

        if ($task->deal_id !== $deal->id) {
            return response()->json([
                'status' => false,
                'message' => translate('Task does not belong to this massage!'),
            ], 403);
        }

        $task->status = 'complete';
        $task->employee_id = Auth::guard('admin')->id();
        $task->save();

        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'task';
        $activity->title = 'Task Completed: ' . $task->name;
        $activity->subject = $task->description ? substr($task->description, 0, 255) : 'Task completed';
        $activity->note_date = now();
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = [
            'task_id' => $task->id,
            'name' => $task->name,
            'status' => 'complete',
        ];
        $activity->save();

        $tasks = $deal->tasks()->latest()->get();
        $activities = $deal->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $taskHtml = view('admin-views.crm.deals.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.deals.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Task marked as complete!'),
            'html' => $taskHtml,
            'activity_html' => $activityHtml,
        ]);
    }





    public function updateTicketDepartment(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id'     => 'required|exists:deals,id',
            'department_id' => 'required|exists:departments,id',
            'priority'      => 'required',
            'reply'         => 'nullable|string'
        ]);

        $deal = Deal::find($request->ticket_id);
        if (!$deal) {
            return response()->json(['status' => false, 'message' => 'Deal not found'], 404);
        }
        $deal->department_id = $request->department_id;
        $deal->priority      = $request->priority;
        $deal->save();
        $departmentName = $deal->department?->name ?? 'N/A';
        $activity = new DealActivity();
        $activity->deal_id    = $deal->id;
        $activity->activity_type = 'department update';
        $activity->title         = ' Assign To : ' . ($departmentName ?? 'N/A');
        $activity->subject       = 'Department changed to ' . $departmentName . ' by ' . auth('admin')->user()->name;
        $activity->note_date     = now();
        $activity->employee_id   = auth('admin')->id();
        $activity->details       = [
            'ticket_id'     => $deal->id,
            'department_id' => $request->department_id,
            'department_name' => $departmentName,
            'priority'      => $deal->priority,
            'reply'         => $request->reply,
        ];
        $activity->save();
        return response()->json([
            'status'  => true,
            'message' => 'Department assigned successfully!'
        ]);
    }
    public function assignEmployee(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:deals,id',
            'employee_id' => 'required|exists:admins,id'
        ]);

        $deal = Deal::find($request->ticket_id);
        if (!$deal) {
            return response()->json(['status' => false, 'message' => 'Deal not found'], 404);
        }

        if (empty($deal->department_id)) {
            return response()->json(['status' => false, 'message' => 'Assign department first.'], 422);
        }

        $employee = Admin::find($request->employee_id);
        if (!$employee) {
            return response()->json(['status' => false, 'message' => 'Employee not found'], 404);
        }

        if ((int)$employee->department_id !== (int)$deal->department_id) {
            return response()->json(['status' => false, 'message' => 'Employee must belong to the selected department.'], 422);
        }

        $deal->employee_id = $request->employee_id;
        $deal->save();

        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'Employee Assign';
        $activity->title = ' Assign To : ' . ($employee->name ?? 'N/A');
        $activity->subject = 'Employee assigned to this deal';
        $activity->note_date = now();
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = [
            'assigned_employee_id' => $employee->id ?? null,
            'assigned_employee_name' => $employee->name ?? 'N/A',
        ];
        $activity->save();
        return response()->json([
            'status' => true,
            'message' => 'Employee assigned successfully!'
        ]);
    }

    public function assignOwner(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:deals,id',
            'owner_id' => 'nullable|exists:admins,id',
            'employee_id' => 'nullable|exists:admins,id'
        ]);
        $ownerId = (int)($request->owner_id ?? $request->employee_id ?? 0);
        if ($ownerId <= 0) {
            return response()->json(['status' => false, 'message' => 'Owner is required'], 422);
        }

        $deal = Deal::find($request->ticket_id);
        if (!$deal) {
            return response()->json(['status' => false, 'message' => 'Deal not found'], 404);
        }

        $owner = Admin::find($ownerId);
        if (!$owner) {
            return response()->json(['status' => false, 'message' => 'Owner not found'], 404);
        }

        if (!$this->isSupervisor($owner)) {
            return response()->json(['status' => false, 'message' => 'Owner must be marked as supervisor in employee profile.'], 422);
        }

        if (!empty($deal->department_id) && (int)$owner->department_id !== (int)$deal->department_id) {
            return response()->json(['status' => false, 'message' => 'Owner must belong to the selected department.'], 422);
        }

        $deal->owner_id = $ownerId;
        $deal->save();

        $activity = new DealActivity();
        $activity->deal_id = $deal->id;
        $activity->activity_type = 'Owner Assign';
        $activity->title = ' Assign To : ' . ($owner->name ?? 'N/A');
        $activity->subject = 'Owner assigned to this deal';
        $activity->note_date = now();
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = [
            'assigned_owner_id' => $owner->id ?? null,
            'assigned_owner_name' => $owner->name ?? 'N/A',
        ];
        $activity->save();

        return response()->json([
            'status' => true,
            'message' => 'Owner assigned successfully!'
        ]);
    }

    public function getEmployeesByDepartment(Request $request)
    {
        $isOwnerAssignment = $request->input('assignment') === 'owner';
        if (empty($request->department_id) && !$isOwnerAssignment) {
            return response()->json([]);
        }

        $filters = [];
        if (!empty($request->department_id)) {
            $filters['department_id'] = $request->department_id;
        }
        $employees = $this->adminRepo->getEmployeeListWhere(
            ['id' => 'desc'],
            null,
            $filters,
            [],
            'all'
        );
        $employees = $employees
            ->filter(fn($employee) => (int)($employee->admin_role_id ?? 0) !== 1)
            ->values();
        if ($isOwnerAssignment) {
            $employees = $employees
                ->filter(fn($employee) => $this->isSupervisor($employee))
                ->values();
        }

        if ($request->filled('head_id')) {
            $employees = $employees->where('id', '!=', $request->head_id)->values();
        }
        return response()->json($employees);
    }

    public function escalateRetail(Request $request): RedirectResponse
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'reason'  => 'required|string|max:1000',
        ]);

        $deal = Deal::findOrFail($request->deal_id);

        $title   = 'Deal Escalated';
        $message = "Deal #{$deal->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.crm.deals.retail.view', $deal->id);

        try {
            $this->escalationService->escalateDeal(
                deal: $deal,
                actorId: (int)auth('admin')->id(),
                reason: (string)$request->reason,
                title: $title,
                message: $message,
                link: $link
            );
        } catch (ValidationException $exception) {
            Toastr::error($exception->errors()['escalation'][0] ?? translate('Request failed.'));
            return back();
        }

        $activity = new DealActivity();
        $activity->deal_id       = $deal->id;
        $activity->employee_id   = auth('admin')->id();
        $activity->activity_type = 'escalated';
        $activity->title         = 'Deal escalated.';
        $activity->details       = $message;
        $activity->subject       = $message;
        $activity->save();

        Toastr::success(translate('Deal escalated successfully'));
        return back();
    }

    public function escalateWholesale(Request $request): RedirectResponse
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'reason'  => 'required|string|max:1000',
        ]);

        $deal = Deal::findOrFail($request->deal_id);

        $title   = 'Deal Escalated';
        $message = "Deal #{$deal->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.crm.deals.wholesale.view', $deal->id); // Wholesale view

        try {
            $this->escalationService->escalateDeal(
                deal: $deal,
                actorId: (int)auth('admin')->id(),
                reason: (string)$request->reason,
                title: $title,
                message: $message,
                link: $link
            );
        } catch (ValidationException $exception) {
            Toastr::error($exception->errors()['escalation'][0] ?? translate('Request failed.'));
            return back();
        }

        $activity = new DealActivity();
        $activity->deal_id       = $deal->id;
        $activity->employee_id   = auth('admin')->id();
        $activity->activity_type = 'escalated';
        $activity->title         = 'Deal escalated.';
        $activity->details       = $message;
        $activity->subject       = $message;
        $activity->save();

        Toastr::success(translate('Deal escalated successfully'));
        return back();
    }

    public function getUserOrders(Request $request)
    {
        $userId = $request->user_id;
        $dealId = $request->deal_id;
        $query = Order::where('customer_id', $userId);

        if ($dealId) {
            $deal = Deal::find($dealId);
            if ($deal && $deal->created_at) {
                $query->where('created_at', '>', $deal->created_at);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->select('id', 'order_amount', 'order_status', 'created_at')
            ->get();

        return response()->json(['orders' => $orders]);
    }
    public function linkOrder(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'order_id' => 'required|exists:orders,id',
        ]);

        $deal = Deal::findOrFail($request->deal_id);

        if ($deal->order_id) {
            return response()->json(['success' => false, 'message' => 'Order already linked!']);
        }

        $order = Order::findOrFail($request->order_id);
        if ($deal->related_party_type !== 'contact') {
            return response()->json([
                'success' => false,
                'message' => 'Only retail deals can be linked with customer orders.',
            ], 422);
        }
        if ((int)$deal->related_party_id !== (int)$order->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order does not belong to this deal customer.',
            ], 422);
        }

        $deal->order_id = $order->id;
        $deal->value = $order->order_amount;
        $deal->stage = 'confirmed_order';

        if (strtolower($order->order_status) === 'delivered') {
            $deal->status = 'won';
            $deal->fulfillment_status = 'fulfilled';
        }

        $deal->save();

        $message = "Order #{$order->id} linked to Deal #{$deal->id}";

        $activity = new DealActivity();
        $activity->deal_id       = $deal->id;
        $activity->employee_id   = auth('admin')->id();
        $activity->activity_type = 'Order Link';
        $activity->title         = 'Order Link To The Deal';
        $activity->details       = $message;
        $activity->subject       = $message;
        $activity->save();

        return response()->json(['success' => true, 'message' => 'Order linked successfully!']);
    }

    private function isSuperAdmin(?Admin $admin): bool
    {
        return (int)($admin?->admin_role_id ?? 0) === 1;
    }

    private function supervisorRoleId(): int
    {
        return defined('DEPARTMENT_HEAD_ROLE_ID') ? (int)DEPARTMENT_HEAD_ROLE_ID : 8;
    }

    private function isSupervisor(?Admin $admin): bool
    {
        return (bool)($admin?->is_supervisor ?? false);
    }

    private function departmentEmployeeRoleId(): int
    {
        return defined('DEPARTMENT_EMPLOYEE_ROLE_ID') ? (int)DEPARTMENT_EMPLOYEE_ROLE_ID : 9;
    }
}

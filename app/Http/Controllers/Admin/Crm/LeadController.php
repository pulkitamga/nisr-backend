<?php

namespace App\Http\Controllers\Admin\Crm;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Enums\WebConfigKey;
use App\Traits\PaginatorTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use App\Enums\ViewPaths\Admin\Leads;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Lead;
use App\Models\WholeSalerBusiness;
use App\Models\User;
use App\Models\LeadActivity;
use App\Models\LeadCall;
use App\Models\LeadFile;
use App\Models\LeadNote;
use App\Models\LeadTask;
use App\Models\Admin;
use App\Models\Order;
use App\Services\LeadConvertService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Crm\EscalationService;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface; // Add this
use Illuminate\Validation\ValidationException;
class LeadController extends BaseController
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
        $query = Lead::with([
            'owner',
            'contact',
            'department',
            'employee',
            'inboxMessages' => function ($q) {
                $q->latest()->limit(1);
            }
        ]);

        $dataLimit = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);

       if ($request->filled('searchValue')) {
        $search = trim($request->searchValue);
        $searchPattern = $this->likePattern($search);
        $phoneSearch = $this->normalizedPhoneSearch($search);

        $query->where(function ($q) use ($searchPattern, $phoneSearch) {

            // 1. Contact (User) se search – naam, email, phone
            $q->orWhereHas('user', function ($sub) use ($searchPattern, $phoneSearch) {
                $sub->where('f_name', 'LIKE', $searchPattern)
                    ->orWhere('l_name', 'LIKE', $searchPattern)
                    ->orWhere('email', 'LIKE', $searchPattern)
                    ->orWhere('phone', 'LIKE', $searchPattern)
                    ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", [$searchPattern])
                    ->orWhereRaw("REPLACE(phone, '+', '') LIKE ?", [$phoneSearch]);
            });

            // 2. Inbox Messages se search – sender ka naam, email, phone, subject
            $q->orWhereHas('inboxMessages', function ($sub) use ($searchPattern) {
                $sub->where('sender_name', 'LIKE', $searchPattern)
                    ->orWhere('sender_email', 'LIKE', $searchPattern)
                    ->orWhere('sender_phone', 'LIKE', $searchPattern)
                    ->orWhere('subject', 'LIKE', $searchPattern)
                    ->orWhere('body', 'LIKE', $searchPattern);
            });

            // 3. Agar contact_id null hai lekin inbox message se match kar raha hai
            $q->orWhereExists(function ($exists) use ($searchPattern) {
                $exists->select(DB::raw(1))
                       ->from('inbox_messages')
                       ->whereColumn('inbox_messages.related_lead_id', 'leads.id')
                       ->where(function ($w) use ($searchPattern) {
                           $w->where('sender_name', 'LIKE', $searchPattern)
                             ->orWhere('sender_email', 'LIKE', $searchPattern)
                             ->orWhere('sender_phone', 'LIKE', $searchPattern)
                             ->orWhere('subject', 'LIKE', $searchPattern);
                       });
            });
        });
    }
        $filterDate = $request->input('filter_date');
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
            $query->where('status', 'new');
        }

        $perPage = ($request->filled('choose_first') && (int)$request->choose_first > 0)
            ? (int)$request->choose_first
            : (int)$dataLimit;

        $lead = $query->latest()->paginate(perPage: $perPage)->appends($request->all());
        $getDepartment  = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );

        return view(Leads::INDEX[VIEW], compact('lead', 'getDepartment', 'employees'));
    }



    public function exportList(Request $request)
    {
        $query = Lead::with([
            'owner',
            'contact',
            'department',
            'employee',
            'inboxMessages' => function ($q) {
                $q->latest()->limit(1);
            }
        ]);

        if ($request->filled('searchValue')) {
            $search = trim($request->searchValue);
            $searchPattern = $this->likePattern($search);
            $phoneSearch = $this->normalizedPhoneSearch($search);

            $query->where(function ($q) use ($searchPattern, $phoneSearch) {
                $q->orWhereHas('user', function ($sub) use ($searchPattern, $phoneSearch) {
                    $sub->where('f_name', 'LIKE', $searchPattern)
                        ->orWhere('l_name', 'LIKE', $searchPattern)
                        ->orWhere('email', 'LIKE', $searchPattern)
                        ->orWhere('phone', 'LIKE', $searchPattern)
                        ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", [$searchPattern])
                        ->orWhereRaw("REPLACE(phone, '+', '') LIKE ?", [$phoneSearch]);
                });

                $q->orWhereHas('inboxMessages', function ($subQ) use ($searchPattern) {
                    $subQ->where('sender_name', 'like', $searchPattern)
                        ->orWhere('sender_email', 'like', $searchPattern)
                        ->orWhere('sender_phone', 'like', $searchPattern)
                        ->orWhere('subject', 'like', $searchPattern)
                        ->orWhere('body', 'like', $searchPattern);
                });

                $q->orWhereExists(function ($exists) use ($searchPattern) {
                    $exists->select(DB::raw(1))
                        ->from('inbox_messages')
                        ->whereColumn('inbox_messages.related_lead_id', 'leads.id')
                        ->where(function ($w) use ($searchPattern) {
                            $w->where('sender_name', 'LIKE', $searchPattern)
                                ->orWhere('sender_email', 'LIKE', $searchPattern)
                                ->orWhere('sender_phone', 'LIKE', $searchPattern)
                                ->orWhere('subject', 'LIKE', $searchPattern);
                        });
                });
            });
        }

        $filterDate = $request->input('filter_date');
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
            $query->where('status', 'new');
        }

        if ($request->filled('choose_first') && $request->choose_first > 0) {
            $leads = $query->latest()->take((int)$request->choose_first)->get();
        } else {
            $leads = $query->latest()->get();
        }

        return Excel::download(new LeadsExport($leads), 'leads.xlsx');
    }

    public function searchParty(Request $request)
    {
        $type = $request->get('party_type');
        $term = $request->get('q');

        if ($type === 'company') {
            $results = WholesalerBusiness::query()
                ->join('users', 'users.id', '=', 'wholesaler_businesses.wholesaler_id')
                ->where(function ($q) use ($term) {
                    $q->where('wholesaler_businesses.company_name', 'LIKE', "%{$term}%")
                        ->orWhere('users.email', 'LIKE', "%{$term}%")
                        ->orWhere('users.phone', 'LIKE', "%{$term}%");
                })
                ->select(
                    'wholesaler_businesses.id',
                    DB::raw("CONCAT(wholesaler_businesses.company_name, ' (', users.email, ' - ', users.phone, ')') as text")
                )
                ->get();
        } else {
            $results = User::where('user_type', 0) // 👈 sirf user_type 0
                ->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%")
                        ->orWhere('email', 'LIKE', "%{$term}%")
                        ->orWhere('phone', 'LIKE', "%{$term}%");
                })
                ->select('id', DB::raw("CONCAT(name, ' (', email, ' - ', phone, ')') as text"))
                ->get();
        }

        return response()->json($results);
    }
public function getUserOrders(Request $request)
{
    $userId = $request->get('user_id');

    if (!$userId) {
        return response()->json([]);
    }

    $orders = Order::where('customer_id', $userId)
        ->whereIn('order_status', ['pending', 'confirmed', 'delivered'])
        ->select('id', 'order_no')
        ->orderByDesc('id')
        ->get();

    return response()->json($orders);
}

    public function convertToDeal(Request $request)
    {
        $request->validate([
            'lead_id'     => 'required|exists:leads,id',
            'party_type'  => 'required|in:company,contact',
            'party_id'    => 'required|integer',
            'value'       => 'nullable|numeric|min:0',
        ], [
            'party_id.required' => translate('Please select any user or company to convert deal'),
            'party_id.integer'  => translate('Please select a valid user or company'),
        ]);

        $lead = Lead::findOrFail($request->lead_id);
        $partyExists = $request->party_type === 'company'
            ? WholeSalerBusiness::where('id', $request->party_id)->exists()
            : User::where('id', $request->party_id)->exists();

        if (!$partyExists) {
            Toastr::error(translate('Please select a valid party from search results before converting'));
            return redirect()->back()->withInput();
        }

        if (empty($lead->department_id) || empty($lead->employee_id) || empty($lead->owner_id)) {
            Toastr::error(translate('Assign department, owner and employee before converting this lead'));
            return redirect()->back()->withInput();
        }

        $authUser = auth('admin')->user();
        $owner = Admin::find($lead->owner_id);

        if (!$owner || !(bool)($owner->is_supervisor ?? false)) {
            Toastr::error(translate('Owner must be marked as supervisor in employee profile'));
            return redirect()->back()->withInput();
        }

        if ((int)$owner->department_id !== (int)$lead->department_id) {
            Toastr::error(translate('Assigned owner must belong to the selected department before conversion'));
            return redirect()->back()->withInput();
        }

        $employee = Admin::find($lead->employee_id);
        if (!$employee) {
            Toastr::error(translate('Assigned employee is invalid. Please assign again before conversion'));
            return redirect()->back()->withInput();
        }

        if ((int)$employee->department_id !== (int)$lead->department_id) {
            Toastr::error(translate('Assigned employee must belong to the selected department before conversion'));
            return redirect()->back()->withInput();
        }

        if (!$this->canManageLead($authUser, $lead)) {
            Toastr::error(translate('You are not authorized to convert this lead'));
            return redirect()->back();
        }

        try {
            $payload = $request->all();
            $payload['owner_id'] = $lead->owner_id;
            $payload['employee_id'] = $lead->employee_id;
            $deal = app(LeadConvertService::class)->convert($lead, $payload);

            $activity = new LeadActivity();
            $activity->lead_id = $lead->id;
            $activity->activity_type = 'conversion';
            $activity->title = 'Lead Converted to Deal';
            $activity->subject = 'Lead converted by ' . $authUser->name;
            $activity->note_date = now();
            $activity->employee_id = $authUser->id;
            $activity->details = [
                'deal_id'         => $deal->id ?? null,
                'party_id'        => $request->party_id,
                'party_type'      => $request->party_type,
                'owner_id'        => $lead->owner_id,
                'value'           => $request->value,
                'quotation_id'    => null,
                'quotation_status' => 'draft',
                'employee_id'     => $lead->employee_id,
            ];
            $activity->save();

            Toastr::success(translate('Lead converted successfully'));
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function disqualify(Request $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'required|exists:leads,id',
        ]);

        $authUser = auth('admin')->user();
        $lead = Lead::findOrFail($request->message_id);

        if (!$this->canManageLead($authUser, $lead)) {
            return response()->json([
                'status'  => false,
                'message' => 'You are not authorized to disqualify this lead.',
            ], 403);
        }

        $lead->status = 'disqualified';
        $lead->save();

        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
        $activity->activity_type = 'disqualification';
        $activity->title = 'Lead Disqualified';
        $activity->subject = 'Lead disqualified by ' . $authUser->name;
        $activity->note_date = now();
        $activity->employee_id = $authUser->id;
        $activity->details = [
            'status' => 'disqualified',
            'lead_id' => $lead->id,
        ];
        $activity->save();

        return response()->json([
            'status'  => true,
            'message' => 'Lead disqualified successfully!',
        ]);
    }
    public function showLead($id)
    {
        $lead = Lead::with([
            'inboxMessages' => function ($q) {
                $q->latest()->first();
            },
            'latestInboxMessage',
            'escalations.escalatedBy',
            'activities',
            'notes',
            'tasks',
            'calls',
            'files',
            'deals',
            'purchaseOrder.items.product'
        ])->findOrFail($id);

        return view(Leads::SHOW[VIEW], compact('lead'));
    }
    public function view($id): JsonResponse
    {
        $lead = Lead::with(['inboxMessages' => function ($q) {
            $q->latest()->first();
        }])->findOrFail($id);

        $inbox = $lead->inboxMessages->first();

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $lead->id,
                'subject' => $inbox?->subject ?? translate('no_Subject'),
                'sender_name' => $inbox?->sender_name ?? translate('Unassigned'),
                'sender_email' => $inbox?->sender_email ?? 'Not Available',
                'sender_phone' => $inbox?->sender_phone ?? 'Not Available',
                'body' => $inbox?->body ?? translate('No message'),
                'created_at' => $lead->created_at->format('d M, Y H:i'),
            ],
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

        $lead = Lead::findOrFail($id);

        $details = $request->input('details');
        if (is_string($details)) {
            $details = ['description' => $details];
        } elseif (!is_array($details)) {
            $details = [];
        }

        $subject = $request->input('subject')
            ?? (isset($details['description']) ? substr((string)$details['description'], 0, 255) : 'Activity logged');

        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
        $activity->activity_type = $request->input('activity_type', 'activity');
        $activity->title = $request->input('title', 'Activity Added');
        $activity->subject = $subject;
        $activity->note_date = $request->input('note_date', now());
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = $details;
        $activity->save();

        $activities = $lead->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $activityHtml = view('admin-views.crm.leads.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Activity saved successfully!'),
            'activity_html' => $activityHtml,
        ]);
    }


    public function storeNote(Request $request, $id): JsonResponse
    {
        $request->validate([
            'note' => 'required|string',
            'noted_at' => 'required|date',
        ]);

        $lead = Lead::findOrFail($id);

        $note = new LeadNote();
        $note->lead_id = $lead->id;
        $note->note = $request->note;
        $note->noted_at = $request->noted_at;
        $note->employee_id = Auth::guard('admin')->id();
        $note->save();

        // Log activity for note
        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
        $activity->activity_type = 'note';
        $activity->title = 'Note Added';
        $activity->subject = substr($request->note, 0, 255); // Truncate for subject
        $activity->note_date = $request->noted_at;
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = ['note_id' => $note->id, 'content' => $request->note];
        $activity->save();

        $notes = $lead->notes()->latest()->get();
        $activities = $lead->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $noteHtml = view('admin-views.crm.leads.partials.note_list', compact('notes'))->render();
        $activityHtml = view('admin-views.crm.leads.partials.activity_list', compact('activities'))->render();

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

        $lead = Lead::findOrFail($id);

        $task = new LeadTask();
        $task->lead_id = $lead->id;
        $task->name = $request->name;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->status = $request->status;
        $task->employee_id = Auth::guard('admin')->id();
        $task->department_id = $lead->department_id;
        $task->save();

        // Log activity for task
        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
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

        // Render the updated task and activity lists
        $tasks = $lead->tasks()->latest()->get();
        $activities = $lead->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $taskHtml = view('admin-views.crm.leads.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.leads.partials.activity_list', compact('activities'))->render();

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

        $lead = Lead::findOrFail($id);

        $call = new LeadCall();
        $call->lead_id = $lead->id;
        $call->title = $request->title;
        $call->from = $request->from;
        $call->to = $request->to;
        $call->guests = $request->guests;
        $call->location = $request->location;
        $call->description = $request->description;
        $call->employee_id = Auth::guard('admin')->id();
        $call->department_id = $lead->department_id;
        $call->save();

        // Log activity for call
        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
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
        $calls = $lead->calls()->latest()->get();
        $activities = $lead->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $callHtml = view('admin-views.crm.leads.partials.call_list', compact('calls'))->render();
        $activityHtml = view('admin-views.crm.leads.partials.activity_list', compact('activities'))->render();

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
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $lead = Lead::findOrFail($id);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (!$this->isAllowedUploadMime($file->getMimeType())) {
                return response()->json([
                    'status' => false,
                    'message' => translate('Invalid file type.'),
                ], 422);
            }

            $extension = $file->extension() ?: $file->getClientOriginalExtension();
            $fileName = now()->timestamp . '_' . Str::random(16) . ($extension ? '.' . strtolower($extension) : '');
            $filePath = $file->storeAs('uploads/lead_files', $fileName, 'public');

            $fileModel = new LeadFile();
            $fileModel->lead_id = $lead->id;
            $fileModel->file = $filePath;
            $fileModel->employee_id = Auth::guard('admin')->id();
            $fileModel->save();

            // Log activity for file
            $activity = new LeadActivity();
            $activity->lead_id = $lead->id;
            $activity->activity_type = 'file';
            $activity->title = 'File Uploaded: ' . $fileName;
            $activity->subject = 'File uploaded for lead';
            $activity->note_date = now();
            $activity->employee_id = Auth::guard('admin')->id();
            $activity->details = [
                'file_id' => $fileModel->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
            ];
            $activity->save();

            // Render the updated file and activity lists
            $files = $lead->files()->latest()->get();
            $activities = $lead->activities()
                ->orderByRaw('COALESCE(updated_at, created_at) DESC')
                ->get();
            $fileHtml = view('admin-views.crm.leads.partials.file_list', compact('files'))->render();
            $activityHtml = view('admin-views.crm.leads.partials.activity_list', compact('activities'))->render();

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
            'task_id' => 'required|exists:lead_task,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,complete',
        ]);

        $lead = Lead::findOrFail($id);
        $task = LeadTask::findOrFail($task_id);

        if ($task->lead_id !== $lead->id) {
            return response()->json([
                'status' => false,
                'message' => translate('Task does not belong to this lead!'),
            ], 403);
        }

        $task->name = $request->name;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->status = $request->status;
        $task->employee_id = Auth::guard('admin')->id();
        $task->save();

        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
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

        $tasks = $lead->tasks()->latest()->get();
        $activities = $lead->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $taskHtml = view('admin-views.crm.leads.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.leads.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Task updated successfully!'),
            'html' => $taskHtml,
            'activity_html' => $activityHtml,
        ]);
    }

    public function completeTask(Request $request, $id, $task_id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $task = LeadTask::findOrFail($task_id);

        if ($task->lead_id !== $lead->id) {
            return response()->json([
                'status' => false,
                'message' => translate('Task does not belong to this lead!'),
            ], 403);
        }

        $task->status = 'complete';
        $task->employee_id = Auth::guard('admin')->id();
        $task->save();

        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
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

        $tasks = $lead->tasks()->latest()->get();
        $activities = $lead->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $taskHtml = view('admin-views.crm.leads.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.leads.partials.activity_list', compact('activities'))->render();

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
            'ticket_id'     => 'required|exists:leads,id',
            'department_id' => 'required|exists:departments,id',
            'priority'      => 'required',
            'reply'         => 'nullable|string'
        ]);

        $lead = Lead::find($request->ticket_id);
        if (!$lead) {
            return response()->json(['status' => false, 'message' => 'Ticket not found'], 404);
        }
        $previousDepartmentId = (int)$lead->department_id;
        $newDepartmentId = (int)$request->department_id;
        $departmentChanged = $previousDepartmentId !== $newDepartmentId;

        $lead->department_id = $newDepartmentId;
        $lead->priority      = $request->priority;
        if ($departmentChanged) {
            $lead->employee_id = null;
        }

        $lead->save();
        $departmentName = $lead->department?->name ?? 'N/A';
        $activity = new LeadActivity();
        $activity->lead_id    = $lead->id;
        $activity->activity_type = 'department update';
        $activity->title         = 'Ticket Department Updated';
        $activity->subject       = 'Department changed to ' . $departmentName . ' by ' . auth('admin')->user()->name;
        $activity->note_date     = now();
        $activity->employee_id   = auth('admin')->id();
        $activity->details       = [
            'ticket_id'     => $lead->id,
            'department_id' => $request->department_id,
            'department_name' => $departmentName,
            'priority'      => $lead->priority,
            'employee_cleared' => $departmentChanged,
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
        return $this->handleLeadAssignmentUpdate($request);
    }

    public function updateAssignment(Request $request): JsonResponse
    {
        return $this->handleLeadAssignmentUpdate($request);
    }

    public function assignOwner(Request $request): JsonResponse
    {
        if (!$request->filled('owner_id') && $request->filled('employee_id')) {
            $request->merge(['owner_id' => $request->input('employee_id')]);
        }
        return $this->handleLeadAssignmentUpdate($request);
    }

    private function handleLeadAssignmentUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:leads,id',
            'department_id' => 'nullable|exists:departments,id',
            'owner_id' => 'nullable|exists:admins,id',
            'employee_id' => 'nullable|exists:admins,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'reply' => 'nullable|string',
        ]);

        $lead = Lead::find($request->ticket_id);
        if (!$lead) {
            return response()->json(['status' => false, 'message' => 'Lead not found'], 404);
        }

        $hasDepartmentUpdate = $request->filled('department_id');
        $hasOwnerUpdate = $request->filled('owner_id');
        $hasEmployeeUpdate = $request->filled('employee_id');
        $hasPriorityUpdate = $request->filled('priority');
        $hasReplyUpdate = $request->filled('reply');

        if (
            !$hasDepartmentUpdate &&
            !$hasOwnerUpdate &&
            !$hasEmployeeUpdate &&
            !$hasPriorityUpdate &&
            !$hasReplyUpdate
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Provide at least one field to update.',
            ], 422);
        }

        $effectiveDepartmentId = $hasDepartmentUpdate
            ? (int)$request->department_id
            : (int)($lead->department_id ?? 0);

        $owner = null;
        if ($hasOwnerUpdate) {
            $owner = Admin::find($request->owner_id);
            if (!$owner) {
                return response()->json(['status' => false, 'message' => 'Owner not found'], 404);
            }
            if (!$this->isSupervisor($owner)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Owner must be marked as supervisor in employee profile.',
                ], 422);
            }
            if ($effectiveDepartmentId > 0 && (int)$owner->department_id !== $effectiveDepartmentId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Owner must belong to the selected department.',
                ], 422);
            }
        }

        $employee = null;
        if ($hasEmployeeUpdate) {
            $employee = Admin::find($request->employee_id);
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee not found'], 404);
            }
            if ($effectiveDepartmentId <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Assign department first.',
                ], 422);
            }
            if ((int)$employee->department_id !== $effectiveDepartmentId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee must belong to the selected department.',
                ], 422);
            }
        }

        $details = [
            'lead_id' => $lead->id,
            'updated_fields' => [],
        ];

        if ($hasDepartmentUpdate) {
            $lead->department_id = (int)$request->department_id;
            $details['department_id'] = (int)$request->department_id;
            $details['updated_fields'][] = 'department_id';
        }
        if ($hasPriorityUpdate) {
            $lead->priority = $request->priority;
            $details['priority'] = $lead->priority;
            $details['updated_fields'][] = 'priority';
        }
        if ($hasReplyUpdate) {
            $details['reply'] = $request->reply;
            $details['updated_fields'][] = 'reply';
        }
        if ($hasOwnerUpdate && $owner) {
            $lead->owner_id = $owner->id;
            $details['owner_id'] = $owner->id;
            $details['owner_name'] = $owner->name;
            $details['updated_fields'][] = 'owner_id';
        }
        if ($hasEmployeeUpdate && $employee) {
            $lead->employee_id = $employee->id;
            $details['employee_id'] = $employee->id;
            $details['employee_name'] = $employee->name;
            $details['updated_fields'][] = 'employee_id';
        }

        if ($hasDepartmentUpdate) {
            if (!$hasOwnerUpdate && $lead->owner && (int)$lead->owner->department_id !== (int)$lead->department_id) {
                $lead->owner_id = null;
                $details['owner_reset'] = true;
            }
            if (!$hasEmployeeUpdate && $lead->employee && (int)$lead->employee->department_id !== (int)$lead->department_id) {
                $lead->employee_id = null;
                $details['employee_reset'] = true;
            }
        }

        $lead->save();

        $activity = new LeadActivity();
        $activity->lead_id = $lead->id;
        $activity->activity_type = 'assignment_update';
        $activity->title = 'Assignment Updated';
        $activity->subject = 'Assignment updated by ' . auth('admin')->user()->name;
        $activity->note_date = now();
        $activity->employee_id = auth('admin')->id();
        $activity->details = $details;
        $activity->save();

        return response()->json([
            'status' => true,
            'message' => 'Assignment updated successfully!',
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
            ->filter(fn($employee) => !$this->isSuperAdmin($employee))
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


    public function escalate(Request $request): RedirectResponse
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'reason'  => 'required|string|max:1000',
        ]);

        $lead = Lead::findOrFail($request->lead_id);

        $title   = 'Lead Escalated';
        $message = "Lead #{$lead->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.crm.lead.show', $lead->id);

        try {
            $this->escalationService->escalateLead(
                lead: $lead,
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

        $activity = new LeadActivity();
        $activity->lead_id       = $lead->id;
        $activity->employee_id   = auth('admin')->id();
        $activity->activity_type = 'escalated';
        $activity->title         = 'Lead escalated.';
        $activity->details       = $message;
        $activity->subject       = $message;
        $activity->save();

        Toastr::success(translate('Lead escalated successfully'));
        return back();
    }

    private function isSuperAdmin(?Admin $admin): bool
    {
        return $admin?->isSuperAdmin() === true;
    }

    private function canManageLead(?Admin $authUser, Lead $lead): bool
    {
        if (!$authUser) {
            return false;
        }

        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        $departmentHeadId = (int)($lead->department?->head_id ?? 0);
        if (!empty($lead->employee_id)) {
            return (int)$lead->employee_id === (int)$authUser->id
                || ($departmentHeadId > 0 && $departmentHeadId === (int)$authUser->id);
        }

        if (!empty($lead->department_id) && $departmentHeadId > 0) {
            return $departmentHeadId === (int)$authUser->id;
        }

        return false;
    }

    private function likePattern(string $value): string
    {
        return '%' . addcslashes($value, '\\%_') . '%';
    }

    private function normalizedPhoneSearch(string $value): string
    {
        return '%' . str_replace('+', '', addcslashes($value, '\\%_')) . '%';
    }

    private function isAllowedUploadMime(?string $mimeType): bool
    {
        return in_array((string)$mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
        ], true);
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

<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Models\InboxMessage;
use App\Http\Controllers\BaseController;
use App\Enums\WebConfigKey;
use App\Traits\PaginatorTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use App\Enums\ViewPaths\Admin\Crm;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\LeadConvert;
use App\Services\TicketConvert;
use App\Models\Lead;
use App\Models\Admin;
use App\Models\InboxActivities;
use App\Models\InboxCall;
use App\Models\InboxFile;
use App\Models\InboxNote;
use App\Models\InboxTask;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\InboxSuggestion;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InboxMessagesExport;
use Carbon\Carbon;
use App\Services\SlaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InboxMessageController extends BaseController
{
    public function __construct(
        private readonly SupportTicketRepositoryInterface       $supportTicketRepo,
        private readonly SupportTicketConvRepositoryInterface   $supportTicketConvRepo,
        private readonly DepartmentRepositoryInterface          $departmentRepo,
        private readonly AdminRepositoryInterface               $adminRepo,
        private readonly SlaService                             $slaService,

    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }
    public function getListView(Request $request)
    {


        $query = InboxMessage::with(['department', 'employee', 'owner']);

        if ($request->filled('searchValue')) {
            $search = trim($request->searchValue);

            $query->where(function ($q) use ($search) {
                $q->where('sender_name', 'LIKE', "%{$search}%")
                    ->orWhere('sender_email', 'LIKE', "%{$search}%")
                    ->orWhere('sender_phone', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('body', 'LIKE', "%{$search}%")

                    ->orWhereExists(function ($exists) use ($search) {
                        $exists->select(DB::raw(1))
                            ->from('users')
                            ->where(function ($w) {
                                $w->whereColumn('users.id', 'inbox_messages.contact_id')
                                    ->orWhereColumn('users.email', 'inbox_messages.sender_email')
                                    ->orWhereRaw("REPLACE(users.phone, '+', '') = REPLACE(inbox_messages.sender_phone, '+', '')")
                                    ->orWhereRaw("REPLACE(users.phone, ' ', '') = REPLACE(inbox_messages.sender_phone, ' ', '')");
                            })
                            ->where(function ($w) use ($search) {
                                $w->where('users.f_name', 'LIKE', "%{$search}%")
                                    ->orWhere('users.l_name', 'LIKE', "%{$search}%")
                                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                                    ->orWhere('users.phone', 'LIKE', "%{$search}%")
                                    ->orWhereRaw("CONCAT(TRIM(users.f_name), ' ', TRIM(users.l_name)) LIKE ?", ["%{$search}%"]);
                            });
                    });
            });
        }
        $getDepartment  = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $dataLimit = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);



        // 📅 Date filter
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
            $query->where('status', 'new');
        }

        // 📡 Channel filter
        if ($request->filled('Channel')) {
            $query->where('pipeline', $request->Channel);
        }

        // 🪄 Custom ordering: "new" first, then others by created_at desc
        $query->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        $perPage = ($request->filled('choose_first') && (int)$request->choose_first > 0)
            ? (int)$request->choose_first
            : (int)$dataLimit;

        $messages = $query->paginate(perPage: $perPage)->appends($request->all());

        return view(Crm::INDEX[VIEW], compact('messages', 'getDepartment', 'employees'));
    }

    public function getUserInfo($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function connectUser(Request $request)
    {
        $msg = InboxMessage::findOrFail($request->message_id);
        $msg->contact_id = $request->user_id;
        $msg->save();

        InboxSuggestion::where('inbox_message_id', $msg->id)->update(['status' => 'connected']);

        return response()->json(['success' => 1]);
    }


    public function exportList(Request $request)
    {
        $query = InboxMessage::with(['department', 'employee', 'owner']);

        if ($request->filled('searchValue')) {
            $search = trim($request->searchValue);
            $query->where(function ($q) use ($search) {
                $q->where('sender_name', 'like', "%{$search}%")
                    ->orWhere('sender_email', 'like', "%{$search}%")
                    ->orWhere('sender_phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereExists(function ($exists) use ($search) {
                        $exists->select(DB::raw(1))
                            ->from('users')
                            ->where(function ($w) {
                                $w->whereColumn('users.id', 'inbox_messages.contact_id')
                                    ->orWhereColumn('users.email', 'inbox_messages.sender_email')
                                    ->orWhereRaw("REPLACE(users.phone, '+', '') = REPLACE(inbox_messages.sender_phone, '+', '')")
                                    ->orWhereRaw("REPLACE(users.phone, ' ', '') = REPLACE(inbox_messages.sender_phone, ' ', '')");
                            })
                            ->where(function ($w) use ($search) {
                                $w->where('users.f_name', 'LIKE', "%{$search}%")
                                    ->orWhere('users.l_name', 'LIKE', "%{$search}%")
                                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                                    ->orWhere('users.phone', 'LIKE', "%{$search}%")
                                    ->orWhereRaw("CONCAT(TRIM(users.f_name), ' ', TRIM(users.l_name)) LIKE ?", ["%{$search}%"]);
                            });
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
            if ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'new');
        }

        if ($request->filled('Channel')) {
            $query->where('pipeline', $request->Channel);
        }

        $query->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        $messages = $query->get();

        return Excel::download(new InboxMessagesExport($messages), 'inbox_messages.xlsx');
    }

    public function showMassage($id)
    {
        $inbox = InboxMessage::with([
            'activities',
            'notes',
            'tasks',
            'calls',
            'files'
        ])->findOrFail($id);

        return view(Crm::SHOW[VIEW], compact('inbox'));
    }



    public function show($id)
    {
        $message = InboxMessage::findOrFail($id);
        return view('inbox.show', compact('message'));
    }

    public function convertInquiry(Request $request)
    {
        $request->validate([
            'message_id'   => 'required|exists:inbox_messages,id',
            'type'         => 'required|in:lead,ticket',
            'sub_type'     => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $message = InboxMessage::findOrFail($request->message_id);
        /** @var Admin|null $authUser */
        $authUser = auth('admin')->user();
        $owner = $message->owner;

        if (!$owner || !$this->isSupervisor($owner)) {
            return response()->json([
                'status' => false,
                'message' => 'Assign a supervisor as owner first.',
            ], 400);
        }
        if (!$this->isSuperAdmin($authUser)) {
            if (
                $message->employee_id !== $authUser->id &&
                $message->department?->head_id !== $authUser->id
            ) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to convert this inquiry.',
                ], 403);
            }
        }

        if (!in_array((string)$message->status, ['new', 'processing'], true)) {
            return response()->json([
                'status'  => false,
                'message' => 'Only new or processing inquiries can be converted.',
            ], 422);
        }

        DB::transaction(function () use ($request, $message, $authUser) {
            if ($request->type === 'lead') {
                if ($message->related_lead_id) {
                    $lead = Lead::find($message->related_lead_id);
                    if (!$lead) {
                        $lead = LeadConvert::fromInboxMessage($message, $request->sub_type, $request->department_id);
                        $message->related_lead_id = $lead->id;
                    }
                } else {
                    $lead = LeadConvert::fromInboxMessage($message, $request->sub_type, $request->department_id);
                    $message->related_lead_id = $lead->id;
                }
            }

            if ($request->type === 'ticket') {
                if ($message->related_ticket_id) {
                    $ticket = SupportTicket::find($message->related_ticket_id);
                    if (!$ticket) {
                        $ticket = TicketConvert::fromInboxMessage($message, $request->sub_type, $request->reason, $request->department_id, $request->priority);
                        $message->related_ticket_id = $ticket->id;
                    }
                } else {
                    $ticket = TicketConvert::fromInboxMessage($message, $request->sub_type, $request->reason, $request->department_id, $request->priority);
                    $message->related_ticket_id = $ticket->id;
                }
            }

            $message->convert_type = $request->type;
            $message->convert_sub_type = $request->sub_type;
            $message->status = 'converted';
            if ($request->filled('department_id')) {
                $message->department_id = $request->department_id;
            }
            if ($request->filled('priority')) {
                $message->priority = $request->priority;
            }
            $message->save();

            $activity = new InboxActivities();
            $activity->massage_id = $message->id;
            $activity->activity_type = 'conversion';
            $activity->title = 'Inquiry Converted to ' . ucfirst($request->type);
            $activity->subject = 'Converted by ' . $authUser->name;
            $activity->note_date = now();
            $activity->employee_id = $authUser->id;
            $activity->details = [
                'message_id' => $message->id,
                'type' => $request->type,
                'sub_type' => $request->sub_type,
                'lead_id' => $message->related_lead_id ?? null,
                'status' => 'converted',
            ];
            $activity->save();
        });

        return response()->json([
            'status'  => true,
            'message' => 'Inquiry converted successfully!',
            'lead_id' => $message->related_lead_id ?? null,
        ]);
    }


    public function convertBulkInquiry(Request $request)
    {
        $request->validate([
            'message_ids' => 'required',
            'type'        => 'required|in:lead,ticket',
            'sub_type'    => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $ids = is_array($request->message_ids)
            ? $request->message_ids
            : explode(',', $request->message_ids);

        $authUser  = auth('admin')->user();
        $converted = [];
        $skipped   = [];

        foreach ($ids as $id) {
            $message = InboxMessage::find($id);

            if (!$message) {
                $skipped[] = $id;
                continue;
            }

            $owner = $message->owner;
            if (!$owner || !$this->isSupervisor($owner)) {
                $skipped[] = $id;
                continue; // ya chaho to separate message bhej sakte ho
            }

            // 🔒 Permission check
            if (!$this->isSuperAdmin($authUser)) {
                if (
                    $message->employee_id !== $authUser->id &&
                    $message->department?->head_id !== $authUser->id
                ) {
                    $skipped[] = $id;
                    continue;
                }
            }

            if (!in_array((string)$message->status, ['new', 'processing'], true)) {
                $skipped[] = $id;
                continue;
            }

            try {
                DB::transaction(function () use ($request, $message, $authUser) {
                    if ($request->type === 'lead') {
                        if ($message->related_lead_id) {
                            $lead = Lead::find($message->related_lead_id);
                            if (!$lead) {
                                $lead = LeadConvert::fromInboxMessage($message, $request->sub_type, $request->department_id);
                                $message->related_lead_id = $lead->id;
                            }
                        } else {
                            $lead = LeadConvert::fromInboxMessage($message, $request->sub_type, $request->department_id);
                            $message->related_lead_id = $lead->id;
                        }
                    }

                    if ($request->type === 'ticket') {
                        if ($message->related_ticket_id) {
                            $ticket = SupportTicket::find($message->related_ticket_id);
                            if (!$ticket) {
                                $ticket = TicketConvert::fromInboxMessage($message, $request->sub_type, $request->reason, $request->department_id, $request->priority);
                                $message->related_ticket_id = $ticket->id;
                            }
                        } else {
                            $ticket = TicketConvert::fromInboxMessage($message, $request->sub_type, $request->reason, $request->department_id, $request->priority);
                            $message->related_ticket_id = $ticket->id;
                        }
                    }

                    $message->convert_type = $request->type;
                    $message->convert_sub_type = $request->sub_type;
                    $message->status = 'converted';
                    if ($request->filled('department_id')) {
                        $message->department_id = $request->department_id;
                    }
                    if ($request->filled('priority')) {
                        $message->priority = $request->priority;
                    }
                    $message->save();

                    $activity = new InboxActivities();
                    $activity->massage_id = $message->id;
                    $activity->activity_type = 'conversion';
                    $activity->title = 'Inquiry Converted to ' . ucfirst($request->type);
                    $activity->subject = 'Converted by ' . $authUser->name;
                    $activity->note_date = now();
                    $activity->employee_id = $authUser->id;
                    $activity->details = [
                        'message_id' => $message->id,
                        'type' => $request->type,
                        'sub_type' => $request->sub_type,
                        'lead_id' => $message->related_lead_id ?? null,
                        'status' => 'converted',
                    ];
                    $activity->save();
                });
                $converted[] = $id;
            } catch (\Throwable) {
                $skipped[] = $id;
                continue;
            }
        }

        return response()->json([
            'status'    => count($converted) > 0,
            'message'   => count($converted) > 1
                ? 'Messages converted successfully!'
                : (count($converted) === 1 ? 'Inquiry converted successfully!' : 'No inquiry converted!'),
            'converted' => $converted,
            'skipped'   => $skipped,
        ]);
    }

    public function updateMessageType(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:inbox_messages,id',
            'message_type' => 'required|in:support,service,career,warranty,contact'
        ]);

        $message = InboxMessage::find($request->message_id);
        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message not found']);
        }

        $message->message_type = $request->message_type;
        if ($message->save()) {
            return response()->json(['success' => true, 'message' => 'Message type updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update message type']);
        }
    }


    public function ignoreMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'required|exists:inbox_messages,id',
        ]);

        $message = InboxMessage::find($request->message_id);
        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Message not found'
            ], 404);
        }

        $message->status = 'ignored';
        $message->save();

        // Create activity
        $activity = new InboxActivities();
        $activity->massage_id   = $message->id;
        $activity->activity_type = 'ignore';
        $activity->title         = 'Message Ignored';
        $activity->subject       = 'Message ignored by ' . auth('admin')->user()->name;
        $activity->note_date     = now();
        $activity->employee_id   = auth('admin')->id();
        $activity->details       = [
            'message_id' => $message->id,
            'status'     => 'ignored',
        ];
        $activity->save();

        return response()->json([
            'status' => true,
            'message' => 'Message ignored successfully!'
        ]);
    }

    public function spamMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'required|exists:inbox_messages,id',
        ]);

        $message = InboxMessage::find($request->message_id);
        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Message not found'
            ], 404);
        }

        $message->status = 'spam';
        $message->spam_score = 10;
        $message->save();

        // Create activity
        $activity = new InboxActivities();
        $activity->massage_id   = $message->id;
        $activity->activity_type = 'spam';
        $activity->title         = 'Message Marked as Spam';
        $activity->subject       = 'Message marked spam by ' . auth('admin')->user()->name;
        $activity->note_date     = now();
        $activity->employee_id   = auth('admin')->id();
        $activity->details       = [
            'message_id' => $message->id,
            'status'     => 'spam',
            'spam_score' => 10,
        ];
        $activity->save();

        return response()->json([
            'status' => true,
            'message' => 'Message Marked Spam Successfully!'
        ]);
    }



    public function destroy($id)
    {
        $message = InboxMessage::findOrFail($id);
        $message->delete(); // Soft delete

        $activity = new InboxActivities();
        $activity->massage_id    = $message->id;
        $activity->activity_type = 'delete';
        $activity->title         = 'Message Deleted';
        $activity->subject       = 'Message deleted by ' . auth('admin')->user()->name;
        $activity->note_date     = now();
        $activity->employee_id   = auth('admin')->id();
        $activity->details       = [
            'message_id' => $message->id,
        ];
        $activity->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted successfully!'
        ]);
    }

    public function storeNewMassage(Request $request)
    {
        $request->validate([
            'subject'        => 'required|string|max:255',
            'sender_name'    => 'required|string|max:255',
            'sender_email'   => 'nullable|email',
            'sender_phone'   => 'nullable|string|max:20',
            'pipeline'       => 'required|in:email,form,chat,social,phone',
            'message_type'   => 'required|in:support,service,career,warranty,contact',
            'details'        => 'nullable|string',
            'message'        => 'nullable|string',
            'attachment'     => 'nullable|file|max:2048',
            'follow_up_date' => 'nullable|date',
        ]);

        $data = [
            'subject'        => $request->input('subject'),
            'sender_name'    => $request->input('sender_name'),
            'sender_email'   => $request->input('sender_email') ?? null,
            'sender_phone'   => $request->input('sender_phone') ?? null,
            'pipeline'       => $request->input('pipeline'),
            'message_type'   => $request->input('message_type'),
            'body'           => $request->input('details'),
            'message'        => $request->input('message'),
            'follow_up_date' => $request->filled('follow_up_date') ? Carbon::parse($request->input('follow_up_date')) : Carbon::now(),
            'status'         => 'new',
            'priority'       => 'normal',
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            if ($file && $file->isValid() && is_string($file->getPathname()) && trim($file->getPathname()) !== '' && is_file($file->getPathname())) {
                try {
                    $path = $file->store('attachments', 'public');
                    if (is_string($path) && trim($path) !== '') {
                        $data['attachment'] = $path;
                    }
                } catch (\Throwable $exception) {
                    logger()->warning('CRM inbox attachment upload failed', [
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }

        $message = InboxMessage::create($data);

        $activity = new InboxActivities();
        $activity->massage_id    = $message->id;
        $activity->activity_type = 'Create Message';
        $activity->title         = 'New Message Created';
        $activity->subject       = 'Message created by ' . auth('admin')->user()->name;
        $activity->note_date     = now();
        $activity->employee_id   = auth('admin')->id();
        $activity->details       = [
            'message_id'    => $message->id,
            'subject'       => $message->subject,
            'sender_name'   => $message->sender_name,
            'sender_email'  => $message->sender_email,
            'sender_phone'  => $message->sender_phone,
            'pipeline'      => $message->pipeline,
            'message_type'  => $message->message_type,
        ];
        $activity->save();

        Toastr::success(translate('Message created successfully!'));
        return redirect()->back();
    }



    // CRMController.php


    public function view($id): JsonResponse
    {
        $inbox = InboxMessage::findOrFail($id);
        return response()->json([
            'status' => true,
            'data' => [
                'id'           => $inbox->id,
                'subject'      => $inbox?->subject ?? translate('No Subject'),
                'sender_name'  => $inbox?->sender_name ?? translate('Unassigned'),
                'sender_email' => $inbox?->sender_email ?? 'Not Available',
                'sender_phone' => $inbox?->sender_phone ?? 'Not Available',
                'body'         => $inbox?->body ?? translate('No message'),
                'created_at'   => $inbox->created_at->format('d M, Y H:i'),
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

        $message = InboxMessage::findOrFail($id);

        $details = $request->input('details');
        if (is_string($details)) {
            $details = ['description' => $details];
        } elseif (!is_array($details)) {
            $details = [];
        }

        $subject = $request->input('subject')
            ?? (isset($details['description']) ? substr((string)$details['description'], 0, 255) : 'Activity logged');

        $activity = new InboxActivities();
        $activity->massage_id = $message->id;
        $activity->activity_type = $request->input('activity_type', 'activity');
        $activity->title = $request->input('title', 'Activity Added');
        $activity->subject = $subject;
        $activity->note_date = $request->input('note_date', now());
        $activity->employee_id = Auth::guard('admin')->id();
        $activity->details = $details;
        $activity->save();

        $activities = $message->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $activityHtml = view('admin-views.crm.inbox.partials.activity_list', compact('activities'))->render();

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

        $lead = InboxMessage::findOrFail($id);

        $note = new InboxNote();
        $note->massage_id = $lead->id;
        $note->note = $request->note;
        $note->noted_at = $request->noted_at;
        $note->employee_id = Auth::guard('admin')->id();
        $note->save();

        $activity = new InboxActivities();
        $activity->massage_id = $lead->id;
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
        $noteHtml = view('admin-views.crm.inbox.partials.note_list', compact('notes'))->render();
        $activityHtml = view('admin-views.crm.inbox.partials.activity_list', compact('activities'))->render();

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

        $lead = InboxMessage::findOrFail($id);

        $task = new InboxTask();
        $task->massage_id = $lead->id;
        $task->name = $request->name;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->status = $request->status;
        $task->employee_id = Auth::guard('admin')->id();
        $task->department_id = $lead->department_id;
        $task->save();

        // Log activity for task
        $activity = new InboxActivities();
        $activity->massage_id = $lead->id;
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

        $tasks = $lead->tasks()->latest()->get();
        $activities = $lead->activities()
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->get();
        $taskHtml = view('admin-views.crm.inbox.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.inbox.partials.activity_list', compact('activities'))->render();

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

        $lead = InboxMessage::findOrFail($id);

        $call = new InboxCall();
        $call->massage_id = $lead->id;
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
        $activity = new InboxActivities();
        $activity->massage_id = $lead->id;
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
        $callHtml = view('admin-views.crm.inbox.partials.call_list', compact('calls'))->render();
        $activityHtml = view('admin-views.crm.inbox.partials.activity_list', compact('activities'))->render();

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

        $lead = InboxMessage::findOrFail($id);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads/inbox_files', $fileName, 'public');

            $fileModel = new InboxFile();
            $fileModel->massage_id = $lead->id;
            $fileModel->file = $filePath;
            $fileModel->employee_id = Auth::guard('admin')->id();
            $fileModel->save();

            $activity = new InboxActivities();
            $activity->massage_id = $lead->id;
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

            $files = $lead->files()->latest()->get();
            $activities = $lead->activities()
                ->orderByRaw('COALESCE(updated_at, created_at) DESC')
                ->get();
            $fileHtml = view('admin-views.crm.inbox.partials.file_list', compact('files'))->render();
            $activityHtml = view('admin-views.crm.inbox.partials.activity_list', compact('activities'))->render();

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
            'task_id' => 'required|exists:inbox_tasks,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,complete',
        ]);

        $lead = InboxMessage::findOrFail($id);
        $task = InboxTask::findOrFail($task_id);

        if ($task->massage_id !== $lead->id) {
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

        $activity = new InboxActivities();
        $activity->massage_id = $lead->id;
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
        $taskHtml = view('admin-views.crm.inbox.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.inbox.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Task updated successfully!'),
            'html' => $taskHtml,
            'activity_html' => $activityHtml,
        ]);
    }

    public function completeTask(Request $request, $id, $task_id): JsonResponse
    {
        $lead = InboxMessage::findOrFail($id);
        $task = InboxTask::findOrFail($task_id);

        if ($task->massage_id !== $lead->id) {
            return response()->json([
                'status' => false,
                'message' => translate('Task does not belong to this massage!'),
            ], 403);
        }

        $task->status = 'complete';
        $task->employee_id = Auth::guard('admin')->id();
        $task->save();

        $activity = new InboxActivities();
        $activity->massage_id = $lead->id;
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
        $taskHtml = view('admin-views.crm.inbox.partials.task_list', compact('tasks'))->render();
        $activityHtml = view('admin-views.crm.inbox.partials.activity_list', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'message' => translate('Task marked as complete!'),
            'html' => $taskHtml,
            'activity_html' => $activityHtml,
        ]);
    }


    public function updateTicketDepartment(Request $request): JsonResponse
    {
        return $this->handleInboxAssignmentUpdate($request);
    }

    public function updateAssignment(Request $request): JsonResponse
    {
        return $this->handleInboxAssignmentUpdate($request);
    }
    public function assignEmployee(Request $request): JsonResponse
    {
        return $this->handleInboxAssignmentUpdate($request);
    }

    public function assignOwner(Request $request): JsonResponse
    {
        if (!$request->filled('owner_id') && $request->filled('employee_id')) {
            $request->merge(['owner_id' => $request->input('employee_id')]);
        }
        return $this->handleInboxAssignmentUpdate($request);
    }

    private function handleInboxAssignmentUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:inbox_messages,id',
            'department_id' => 'nullable|exists:departments,id',
            'owner_id' => 'nullable|exists:admins,id',
            'employee_id' => 'nullable|exists:admins,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'reply' => 'nullable|string',
        ]);

        $ticket = InboxMessage::find($request->ticket_id);
        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Ticket not found'], 404);
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
            : (int)($ticket->department_id ?? 0);

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
            'ticket_id' => $ticket->id,
            'updated_fields' => [],
        ];

        if ($hasDepartmentUpdate) {
            $ticket->department_id = (int)$request->department_id;
            $details['department_id'] = (int)$request->department_id;
            $details['updated_fields'][] = 'department_id';
        }
        if ($hasPriorityUpdate) {
            $ticket->priority = $request->priority;
            $details['priority'] = $ticket->priority;
            $details['updated_fields'][] = 'priority';
        }
        if ($hasReplyUpdate) {
            $ticket->message = $request->reply;
            $details['reply'] = $ticket->message;
            $details['updated_fields'][] = 'reply';
        }
        if ($hasOwnerUpdate && $owner) {
            $ticket->owner_id = $owner->id;
            $details['owner_id'] = $owner->id;
            $details['owner_name'] = $owner->name;
            $details['updated_fields'][] = 'owner_id';
        }
        if ($hasEmployeeUpdate && $employee) {
            $ticket->employee_id = $employee->id;
            $details['employee_id'] = $employee->id;
            $details['employee_name'] = $employee->name;
            $details['updated_fields'][] = 'employee_id';
        }

        // If department changed and current owner/employee no longer matches, clear stale assignments.
        if ($hasDepartmentUpdate) {
            if (!$hasOwnerUpdate && $ticket->owner && (int)$ticket->owner->department_id !== (int)$ticket->department_id) {
                $ticket->owner_id = null;
                $details['owner_reset'] = true;
            }
            if (!$hasEmployeeUpdate && $ticket->employee && (int)$ticket->employee->department_id !== (int)$ticket->department_id) {
                $ticket->employee_id = null;
                $details['employee_reset'] = true;
            }
        }

        $ticket->save();

        $activity = new InboxActivities();
        $activity->massage_id = $ticket->id;
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

    private function isSuperAdmin(?Admin $admin): bool
    {
        return $admin?->isSuperAdmin() === true;
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

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Models\SupportTicketDepartmentEmployee;
use App\Models\SupportTicketNotification;
use App\Models\CronSenderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPendingTaskNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:pending-tasks-notification'; // Command name to run in terminal
    protected $description = 'Send notifications for pending tasks based on the schedule';

    private SupportTicketRepositoryInterface $supportTicketRepo;
    private SupportTicketConvRepositoryInterface $supportTicketConvRepo;

    public function __construct(
        SupportTicketRepositoryInterface $supportTicketRepo,
        SupportTicketConvRepositoryInterface $supportTicketConvRepo
    ) {
        parent::__construct();
        $this->supportTicketRepo = $supportTicketRepo;
        $this->supportTicketConvRepo = $supportTicketConvRepo;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Filter by date only, ignoring time
        $dateToday = Carbon::today()->format('Y-m-d'); // Format date for display
        $this->info("📅 Checking for pending task notifications for: {$dateToday}...");

        // Get tasks first to check if any exist
        $tasksExist = CronSenderDetail::whereDate('send_date', $dateToday)
            ->where('status', 0)
            ->where('is_active', 0)
            ->exists();

        if (!$tasksExist) {
            $this->warn("⚠️  No pending tasks found for today ({$dateToday}).");
            Log::info("No pending tasks found for today ({$dateToday}).");
            return;
        }

        // Process pending tasks efficiently in chunks to avoid memory overload
        CronSenderDetail::whereDate('send_date', $dateToday)
            ->where('status', 0)
            ->where('is_active', 0)
            ->chunkById(50, function ($tasks) {
                foreach ($tasks as $task) {
                    try {
                        $ticketData = $this->supportTicketRepo->getFirstWhere(
                            params:['id'=>$task->ticket_id],
                            relations: ['department'],
                        );
                        $conversation = SupportTicketDepartmentEmployee::where([
                            'ticket_id' => $task->ticket_id,
                            'status_id' => $ticketData->status,
                            'status_type_id' => $task->ticket_status
                        ])
                        ->where('id', '>', function ($query) use ($task, $ticketData) {
                            $query->select('id')
                                ->from('support_ticket_department_employee')
                                ->where([
                                    'ticket_id' => $task->ticket_id
                                ])
                                ->orderBy('id', 'desc')
                                ->limit(1);
                        })->get();

                        if ($conversation->isNotEmpty()) {
                            $task->update(['status' => 2]);
                            Log::info("Updated cron status to ticket suceessfully proceesed for ticket ID: {$task->ticket_id}");
                            continue; // Skip further processing and move to the next task
                        }

                        SupportTicketNotification::create([
                            'ticket_id' => $task->ticket_id,
                            'notification_for' => $task->send_for, //0 - Dept Head 1 - Dept Emp, 2 - Customer
                            'user_id' => $task->send_for == 0 || $task->send_for == 1 ? $task->sender_id : 0,
                            'customer_id' => $task->send_for == 2 ? $task->sender_id : 0,
                            'title' => $task->title,
                            'message' => $task->message,
                            'status' => 0,
                            'is_Active' => 0
                        ]);                      
                        $task->update(['status' => 1]);

                        // Log successful notification
                        Log::info("Notification sent for ticket ID: {$task->ticket_id}");
                    } catch (\Exception $e) {
                        Log::error("Error processing task ID {$task->id}: " . $e->getMessage());
                    }
                }
            });
        $this->info("✅ Pending task notifications processed for: {$dateToday}.");
    }
}

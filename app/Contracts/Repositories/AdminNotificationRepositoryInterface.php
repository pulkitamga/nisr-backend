<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection; // <-- changed

interface AdminNotificationRepositoryInterface
{
    public function create(array $data);
    public function find($id);
    public function markAsRead($id);

    // unread
    public function getUnreadForEmployee(int $employeeId): Collection;
    public function getUnreadForDepartment(int $departmentId): Collection;
    public function getUnreadForCustomer(int $customerId): Collection;

    // bulk
    public function notifyRecipients(
        $notifiableId,
        string $notifiableType,
        string $title,
        string $message,
        ?string $link,
        array $recipients
    ): Collection;   

    // employee UI
    public function getForEmployee(int $employeeId, array $statuses = [0, 1], int $limit = 50): Collection;
    public function getForUser(int $employeeId, int $limit = 50): Collection;
    public function getAllForEmployee(int $employeeId, array $filters = [], int $paginate = 20);

    // customer UI
    public function getForCustomer(int $customerId, array $statuses = [0, 1], int $limit = 50): Collection;
}
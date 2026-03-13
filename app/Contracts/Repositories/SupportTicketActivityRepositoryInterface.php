<?php

namespace App\Contracts\Repositories;

interface SupportTicketActivityRepositoryInterface
{
    public function add(array $data);
    public function getListWhere(array $filters = [], array $relations = [], string $dataLimit = 'all');
    // Add more if needed (update, delete)
}
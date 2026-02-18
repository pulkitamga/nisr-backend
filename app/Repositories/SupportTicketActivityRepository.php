<?php

namespace App\Repositories;

use App\Contracts\Repositories\SupportTicketActivityRepositoryInterface;
use App\Models\SupportTicketActivity;

class SupportTicketActivityRepository implements SupportTicketActivityRepositoryInterface
{
    public function add(array $data)
    {
        return SupportTicketActivity::create($data);
    }

    public function getListWhere(array $filters = [], array $relations = [], string $dataLimit = 'all')
    {
        $query = SupportTicketActivity::with($relations)->where($filters);
        return $dataLimit == 'all' ? $query->get() : $query->paginate(getWebConfig('pagination_limit'));
    }
}
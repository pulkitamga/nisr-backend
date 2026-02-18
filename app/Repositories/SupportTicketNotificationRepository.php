<?php

namespace App\Repositories;

use App\Contracts\Repositories\SupportTicketNotificationRepositoryInterface;
use App\Models\SupportTicketNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SupportTicketNotificationRepository implements SupportTicketNotificationRepositoryInterface
{
    public function __construct(
        private readonly SupportTicketNotification $notifications,
    )
    {
    }
    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
        $query = $this->notifications->with($relations)->where($filters)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });
        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function add(array $data): string|object
    {
        return $this->notifications->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->notifications->with($relations)
            ->when(isset($params['identity']),function ($query) use ($params){
                return $query->where(['email' => $params['identity']])
                    ->orWhere(['phone' => $params['identity']]);
            })
            ->when(isset($params['id']),function ($query) use ($params){
                return $query->where(['id' => $params['id']]);
            })
            ->when(isset($params['withCount']),function ($query)use($params){
                return $query->withCount($params['withCount']);
            })
            ->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->notifications->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }


    public function update(string $id, array $data): bool
    {
        return $this->notifications->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->notifications->where($params)->delete();
        return true;
    }

}

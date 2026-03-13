<?php

namespace App\Repositories;

use App\Contracts\Repositories\DeliveryStateRepositoryInterface;
use App\Models\DeliveryState;
use App\Traits\ProductTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliveryStateRepository implements DeliveryStateRepositoryInterface
{
    use ProductTrait;

    public function __construct(
        private readonly DeliveryState $deliveryState,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->deliveryState->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->deliveryState->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->deliveryState->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->deliveryState
            ->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->orWhere('state', 'like', "%$searchValue%");
            })
            ->when(isset($filters['state']), function ($query) use ($filters) {
                return $query->where(['state' => $filters['state']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        $this->deliveryState->where('id', $id)->update($data);
        return true;
    }

    public function delete(array $params): bool
    {
        return $this->deliveryState->where($params)->delete();
    }
}

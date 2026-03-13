<?php

namespace App\Repositories;

use App\Contracts\Repositories\DeliveryAreaRepositoryInterface;
use App\Models\DeliveryArea;
use App\Traits\ProductTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliveryAreaRepository implements DeliveryAreaRepositoryInterface
{
    use ProductTrait;

    public function __construct(
        private readonly DeliveryArea $deliveryArea,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->deliveryArea->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->deliveryArea->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->deliveryArea->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->deliveryArea
            ->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->orWhere('zipcode', 'like', "%$searchValue%");
            })
            ->when(isset($filters['zipcode']), function ($query) use ($filters) {
                return $query->where(['zipcode' => $filters['zipcode']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        $this->deliveryArea->where('id', $id)->update($data);
        return true;
    }

    public function delete(array $params): bool
    {
        return $this->deliveryArea->where($params)->delete();
    }
}

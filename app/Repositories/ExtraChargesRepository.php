<?php

namespace App\Repositories;

use App\Contracts\Repositories\ExtraChargesRepositoryInterface;
use App\Models\DeliveryZipCode;
use App\Models\Branch;
use App\Models\Manager;
use App\Models\Admin;
use App\Models\ManageExtraCharge;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ExtraChargesRepository implements ExtraChargesRepositoryInterface
{
    public function __construct(
        private readonly ManageExtraCharge $manageExtraCharge,
    )
    {
    }

    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator
    {
        return $this->manageExtraCharge->with($relations)->whereNotIn('status', [$status])->paginate($paginateBy);
    }

    public function add(array $data): string|object
    {
        return $this->manageExtraCharge->create($data);
    }

     public function addToAdmin(array $data): string|object
    {
        return $this->admin->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->manageExtraCharge->with($relations)
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
        $query = $this->manageExtraCharge->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
        $query = $this->manageExtraCharge->with($relations)->where($filters)
            ->when($searchValue, function ($query) use ($searchValue) {
                $searchTerms = explode(' ', $searchValue);
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) 
                    {
                        $query->orWhere('branch_name', 'like', "%$term%")
                            ->orWhere('branch_address', 'like', "%$term%")
                            ->orWhere('phone', 'like', "%$term%")
                            ->orWhere('email', 'like', "%$term%");
                    }
                });
            })
            /*->when(!empty($relations) && in_array('product', $relations), function ($query) {
                $query->withCount('product');
            })
            ->when(!empty($relations) && in_array('orders', $relations), function ($query) {
                $query->withCount('orders');
            })*/
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->manageExtraCharge->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->manageExtraCharge->where($params)->delete();
        return true;
    }
}

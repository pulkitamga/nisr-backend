<?php

namespace App\Repositories;

use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Models\DeliveryZipCode;
use App\Models\Branch;
use App\Models\Manager;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ManageBranchProductStock;


class BranchRepository implements BranchRepositoryInterface
{
    public function __construct(
        private readonly Branch $branch,
        private readonly Manager $manager,
        private readonly Admin $admin,
    ) {}

    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator
    {
        return $this->branch->with($relations)->whereNotIn('status', [$status])->paginate($paginateBy);
    }

    public function addManager(array $data): string|object
    {
        return $this->manager->create($data);
    }
    public function updateManager(string $id, array $data): bool
    {
        return Manager::where('branch_id', $id)
            ->update($data);
    }
    public function getManager(array $params)
    {
        return  Manager::where('branch_id', $params['branch_id'])
            ->select('name', 'phone', 'email')
            ->get()->first();;
    }
    public function getAdminData(array $params)
    {
        return  Admin::where('branch_id', $params['branch_id'])
            ->select('id', 'branch_id')
            ->get()->first();;
    }
    public function add(array $data): string|object
    {
        return $this->branch->create($data);
    }

    public function addToAdmin(array $data): string|object
    {
        return $this->admin->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->branch->with($relations)
            ->when(isset($params['identity']), function ($query) use ($params) {
                return $query->where(['email' => $params['identity']])
                    ->orWhere(['phone' => $params['identity']]);
            })
            ->when(isset($params['id']), function ($query) use ($params) {
                return $query->where(['id' => $params['id']]);
            })
            ->when(isset($params['withCount']), function ($query) use ($params) {
                return $query->withCount($params['withCount']);
            })
            ->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->branch->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->branch->with($relations)->where($filters)
            ->when($searchValue, function ($query) use ($searchValue) {
                $searchTerms = explode(' ', $searchValue);
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
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
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->branch->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->branch->where($params)->delete();
        return true;
    }

    public function addProductStockToBranch(array $data): ManageBranchProductStock
    {
        return ManageBranchProductStock::create([
            'branch_id' => $data['branch_id'],  
            'product_id' => $data['product_id'],  
            'attributes' => $data['attributes'] ?? '',  
            'current_stock' => $data['current_stock'] , 
        ]);
    }
}

<?php

namespace App\Repositories;

use App\Contracts\Repositories\StockRequestRepositoryInterface;
use App\Models\DeliveryZipCode;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockRequests;
use App\Models\StockRequestProduct;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class StockRequestRepository implements StockRequestRepositoryInterface
{
    public function __construct(
        private readonly Branch $branch,
        private readonly Product $product,
        private readonly StockRequests $stockRequests,
        private readonly StockRequestProduct $stockRequestProduct,
    )
    {
    }

    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator
    {
        return $this->stockRequests->with($relations)->whereNotIn('status', [$status])->paginate($paginateBy);
    }

    public function add(array $data): string|object
    {
        return $this->stockRequests->create($data);
    }

    public function stockRequestProduct(array $data): string|object
    {
        return $this->stockRequestProduct->create($data);
    }


    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->stockRequests->with($relations)
            ->when(isset($params['id']),function ($query) use ($params){
                return $query->where(['id' => $params['id']]);
            })
            ->when(isset($params['withCount']),function ($query)use($params){
                return $query->withCount($params['withCount']);
            })
            ->first();
    }

    public function getStockReqProductFirstWhere(array $params, array $relations = [])
    {
         return StockRequestProduct::where($params)
            ->with($relations) // Load the specified relationships
            ->first(); // Retrieve the first matching record
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->stockRequests->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
        $query = $this->stockRequests->with($relations)->where($filters)
            ->when($searchValue, function ($query) use ($searchValue) {
                $searchTerms = explode(' ', $searchValue);
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) 
                    {
                        $query->orWhere('product_name', 'like', "%$term%")
                            ->orWhere('product_sku', 'like', "%$term%")
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
        return $this->stockRequests->find($id)->update($data);
    }

    public function updateStockRequestProduct(string $id, array $data): bool
    {
        return $this->stockRequestProduct->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->stockRequests->where($params)->delete();
        return true;
    }

    public function deleteStockRequestProduct(array $params): bool
    {
        $this->stockRequestProduct->where($params)->delete();
        return true;
    }
}

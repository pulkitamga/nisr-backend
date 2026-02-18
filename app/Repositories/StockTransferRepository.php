<?php

namespace App\Repositories;

use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfers;
use App\Models\StockTransferProduct;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class StockTransferRepository implements StockTransferRepositoryInterface
{
    public function __construct(
        private readonly Branch $branch,
        private readonly Product $product,
        private readonly StockTransfers $stockTransfers,
        private readonly StockTransferProduct $stockTransferProduct,
    )
    {
    }

    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator
    {
        return $this->stockTransfers->with($relations)->whereNotIn('status', [$status])->paginate($paginateBy);
    }

    public function add(array $data): string|object
    {
        return $this->stockTransfers->create($data);
    }

    public function stockTransferProduct(array $data): string|object
    {
        return $this->stockTransferProduct->create($data);
    }


    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->stockTransfers->with($relations)
            ->when(isset($params['id']),function ($query) use ($params){
                return $query->where(['id' => $params['id']]);
            })
            ->when(isset($params['withCount']),function ($query)use($params){
                return $query->withCount($params['withCount']);
            })
            ->first();
    }

    public function getStockReqProductFirstWhere(array $params, array $relations = []): ?Model
    {
         return StockRequestProduct::where($params)
            ->with($relations) // Load the specified relationships
            ->first(); // Retrieve the first matching record
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->stockTransfers->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
        $query = $this->stockTransfers->with($relations)->where($filters)
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
        return $this->stockTransfers->find($id)->update($data);
    }

    public function updateStockTransferProduct(string $id, array $data): bool
    {
        return $this->stockTransferProduct->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->stockTransfers->where($params)->delete();
        return true;
    }

    public function deleteStockRequestProduct(array $params): bool
    {
        $this->stockTransferProduct->where($params)->delete();
        return true;
    }
}

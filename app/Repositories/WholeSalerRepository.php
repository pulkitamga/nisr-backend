<?php

namespace App\Repositories;

use App\Contracts\Repositories\WholeSalerRepositoryInterface;
use App\Models\WholeSalerBusiness;
use App\Models\WholeSaleProducts;
use App\Models\Product;
use App\Models\Admin;
use App\Models\WholesaleProductPriceRange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class WholeSalerRepository implements WholeSalerRepositoryInterface
{
    public function __construct(
        private readonly WholeSalerBusiness $wholeSalerBusiness,
        private readonly WholeSaleProducts $wholeSaleProducts,
        private readonly WholesaleProductPriceRange $wholesaleProductPriceRange,
        private readonly Product $product,
        private readonly Admin $admin,
    )
    {
    }

    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator
    {
        return $this->wholeSalerBusiness->with($relations)->whereNotIn('status', [$status])->paginate($paginateBy);
    }
    public function add(array $data): string|object
    {
        return $this->wholeSalerBusiness->create($data);
    }

     public function addToAdmin(array $data): string|object
    {
        return $this->admin->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->wholeSalerBusiness->with($relations)
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
        $query = $this->wholeSalerBusiness->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
        $query = $this->wholeSalerBusiness->with($relations)->where($filters)
            ->when($searchValue, function ($query) use ($searchValue) {
                $product_ids = $this->product
                ->where('name', 'like', "%{$searchValue}%")
                ->pluck('id');
                return $query->where('product_id', 'like', "%{$product_ids}%")->orWhereIn('product_id', $product_ids);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }


    public function update(string $id, array $data): bool
    {
        return $this->wholeSalerBusiness->find($id)->update($data);
    }


    public function delete(array $params): bool
    {
        $this->wholeSalerBusiness->where($params)->delete();
        return true;
    }
}

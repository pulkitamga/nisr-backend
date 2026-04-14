<?php

namespace App\Repositories;

use App\Contracts\Repositories\ServiceRepositoryInterface;
use App\Models\Service;
use App\Traits\CacheManagerTrait;
use App\Traits\ProductTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceRepository implements ServiceRepositoryInterface
{

    public function __construct(
        private readonly Service           $service,

    ) {}
    public function add(array $data): Service
    {
        return Service::create($data);
    }
    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->service->where($params)->with($relations)->first();
    }
    public function update(string $id, array $data): bool
    {
        cacheRemoveByType(type: 'products');
        return $this->service
            ->where('id', $id)
            ->update($data);
    }
}

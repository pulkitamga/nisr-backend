<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;


interface StockRequestRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $status
     * @param array $relations
     * @param int $paginateBy
     * @return Collection|array|LengthAwarePaginator
     */
    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator;

    public function updateStockRequestProduct(string $id, array $data): bool;

    public function getStockReqProductFirstWhere(array $params, array $relations = []);

}

<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StockTransferRepositoryInterface extends RepositoryInterface
{
    /**
     * Get records except a particular status
     *
     * @param string $status
     * @param array $relations
     * @param int $paginateBy
     * @return Collection|array|LengthAwarePaginator
     */
    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator;

    /**
     * Store stock transfer product info
     *
     * @param array $data
     * @return mixed
     */
    public function stockTransferProduct(array $data);

    /**
     * Get first stock request product with conditions and relations
     *
     * @param array $params
     * @param array $relations
     * @return Model|null
     */
}

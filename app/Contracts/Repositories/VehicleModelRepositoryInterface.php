<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface VehicleModelRepositoryInterface
{
    public function add(array $data): object|string;

    public function all();
    public function update(int|string $id, array $data): object|string;

    public function delete(int|string $id): bool;

    public function statusChange(int|string $id): bool;
    public function deleteWhere(array $conditions): bool;

    public function getFirstWhere(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model;

    public function getList(
        array $orderBy = [],
        array $relations = [],
        int|string $dataLimit = 100,
        int|null $offset = null
    ): \Illuminate\Support\Collection;

    public function getListWhere(
        array $orderBy = [],
        string|null $searchValue = null,
        array $filters = [],
        array $relations = [],
        int|string $dataLimit = 100,
        int|null $offset = null
    ): \Illuminate\Support\Collection;
}

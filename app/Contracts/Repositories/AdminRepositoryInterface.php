<?php

namespace App\Contracts\Repositories;

interface AdminRepositoryInterface extends RepositoryInterface
{
 public function getEmployeeListWhere(
        array $orderBy = [],
        string $searchValue = null,
        array $filters = [],
        array $relations = [],
        int|string $dataLimit = DEFAULT_DATA_LIMIT,
        int $offset = null
    );
}

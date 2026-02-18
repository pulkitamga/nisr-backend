<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 *
 */
interface ServiceRepositoryInterface
{
    public function add(array $data): Service;

         public function update(string $id, array $data): bool;

}

<?php

namespace App\Repositories;

use App\Contracts\Repositories\ServiceRequestRepositoryInterface;
use App\Models\ServiceRequest;
use App\Traits\CacheManagerTrait;
use App\Traits\ProductTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceRequestRepository implements ServiceRequestRepositoryInterface
{

     public function __construct(
        private readonly ServiceRequest           $service,
     
    ) {}

      public function create(array $data): ServiceRequest
    {
        return $this->service->create($data);
    }

}
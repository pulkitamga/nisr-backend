<?php

namespace App\Contracts\Repositories;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ServiceRequest;



interface ServiceRequestRepositoryInterface 
{
    public function create(array $data): ServiceRequest;


}

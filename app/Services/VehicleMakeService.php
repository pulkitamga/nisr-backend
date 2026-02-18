<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Repositories\VehicleMakeRepositoryInterface;

class VehicleMakeService
{
    protected $vehicleMakeRepo;

    public function __construct(VehicleMakeRepositoryInterface $vehicleMakeRepo)
    {
        $this->vehicleMakeRepo = $vehicleMakeRepo;
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|unique:vehicle_makes,name',
        ])->validate();

        return $this->vehicleMakeRepo->create($validated);
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|unique:vehicle_makes,name,' . $id,
        ])->validate();

        return $this->vehicleMakeRepo->update($id, $validated);
    }

     public function delete(int|string $id): bool
    {
        return $this->vehicleMakeRepo->delete($id);
    }
}

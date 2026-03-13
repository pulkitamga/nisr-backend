<?php

namespace App\Repositories;

use App\Contracts\Repositories\VehicleMakeRepositoryInterface;
use App\Models\VehicleMake;

class VehicleMakeRepository implements VehicleMakeRepositoryInterface
{
    public function all()
    {
        return VehicleMake::with('models')->get();
    }

    public function find($id)
    {
        return VehicleMake::findOrFail($id);
    }

    public function create(array $data)
    {
        return VehicleMake::create($data);
    }

    public function update($id, array $data)
    {
        $make = VehicleMake::findOrFail($id);
        $make->update($data);
        return $make;
    }

    public function delete($id)
    {
        return VehicleMake::destroy($id);
    }

    public function toggleStatus($id)
    {
        $make = VehicleMake::findOrFail($id);
        $make->status = !$make->status;
        $make->save();
        return $make;
    }

}

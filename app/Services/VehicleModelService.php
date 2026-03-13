<?php

namespace App\Services;

use App\Contracts\Repositories\VehicleModelRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VehicleModelService
{
    protected $vehicleModelRepo;

    public function __construct(VehicleModelRepositoryInterface $vehicleModelRepo)
    {
        $this->vehicleModelRepo = $vehicleModelRepo;
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        return $this->vehicleModelRepo->add($data);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateRequest($request);
        return $this->vehicleModelRepo->update(id: $id, data: $data);
    }

    public function delete($id)
    {
        return $this->vehicleModelRepo->delete($id);
    }


    public function getById($id)
    {
        return $this->vehicleModelRepo->getFirstWhere(['id' => $id]);
    }

    protected function validateRequest(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'make_id' => 'required|exists:vehicle_makes,id',
            'name'    => 'required|string|max:255',
            'year'    => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function deleteByMakeId($makeId)
{
    return $this->vehicleModelRepo->deleteWhere(['make_id' => $makeId]);
}

}
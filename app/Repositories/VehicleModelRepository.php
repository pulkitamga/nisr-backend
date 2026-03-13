<?php

namespace App\Repositories;

use App\Contracts\Repositories\VehicleModelRepositoryInterface;
use App\Models\VehicleModel;


class VehicleModelRepository implements VehicleModelRepositoryInterface
{
    public function add(array $data): object|string
    {
        return VehicleModel::create($data);
    }

     public function all()
    {
        return VehicleModel::all();
    }

    public function update(int|string $id, array $data): object|string
    {
        $model = VehicleModel::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int|string $id): bool
    {
        return VehicleModel::where('id', $id)->delete();
    }

    public function statusChange(int|string $id): bool
    {
        $model = VehicleModel::findOrFail($id);
        $model->is_active = !$model->is_active;
        return $model->save();
    }

    public function getFirstWhere(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model
    {
        return VehicleModel::with($relations)->where($params)->first();
    }

    public function getList(
        array $orderBy = [],
        array $relations = [],
        int|string $dataLimit = 100,
        int|null $offset = null
    ): \Illuminate\Support\Collection {
        $query = VehicleModel::with($relations);

        if ($orderBy) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        if ($offset) {
            $query->skip($offset);
        }

        return $query->limit($dataLimit)->get();
    }

    public function getListWhere(
        array $orderBy = [],
        string|null $searchValue = null,
        array $filters = [],
        array $relations = [],
        int|string $dataLimit = 100,
        int|null $offset = null
    ): \Illuminate\Support\Collection {
        $query = VehicleModel::with($relations)->where($filters);

        if ($searchValue) {
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        if ($orderBy) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        if ($offset) {
            $query->skip($offset);
        }

        return $query->limit($dataLimit)->get();
    }

    public function deleteWhere(array $conditions): bool
{
    return VehicleModel::where($conditions)->delete();
}

}
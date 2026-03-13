<?php

namespace App\Repositories;

use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminRepository implements AdminRepositoryInterface
{
    public function __construct(
        private readonly Admin         $admin,
    ) {}

    public function add(array $data): string|object
    {
        return $this->admin->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->admin->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->admin->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }
    public function getById($id)
    {
        return Admin::find($id);
    }


    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->admin->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where('name', 'like', "%$searchValue%")
                    ->orWhere('phone', 'like', "%$searchValue%")
                    ->orWhere('email', 'like', "%$searchValue%");
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->admin->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->admin->where($params)->delete();
        return true;
    }


    public function getEmployeeListWhere(
        array $orderBy = [],
        string $searchValue = null,
        array $filters = [],
        array $relations = [],
        int|string $dataLimit = DEFAULT_DATA_LIMIT,
        int $offset = null
    ): Collection|LengthAwarePaginator {
        $query = $this->admin
            ->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where('name', 'like', "%$searchValue%")
                    ->orWhere('phone', 'like', "%$searchValue%")
                    ->orWhere('email', 'like', "%$searchValue%");
            })
            ->active()
            ->when(isset($filters['role_id']) && $filters['role_id'] != 'all', function ($query) use ($filters) {
                $query->whereHas('roles', function ($roleQuery) use ($filters) {
                    $roleQuery->where('roles.id', $filters['role_id'])
                        ->where('guard_name', config('permissions_admin.guard', 'admin'));
                });
            })
            ->when(!empty($filters['role_name']), function ($query) use ($filters) {
                $query->withRole($filters['role_name']);
            })

            ->when(!empty($filters['department_id']), function ($query) use ($filters) {
                $query->inDepartment($filters['department_id']);
            })
            ->when(!empty($filters['branch_id']), function ($query) use ($filters) {
                $query->where('branch_id', $filters['branch_id']);
            })

            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }
}

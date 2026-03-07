<?php

namespace App\Repositories;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Models\Departments;
use App\Models\DepartmentUsers;
use App\Models\Manager;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function __construct(
        private readonly Departments $departments,
        private readonly DepartmentUsers $departmentUsers,
        private readonly Manager $manager,
        private readonly Admin $admin,
    )
    {
    }

    public function getByStatusExcept(string $status, array $relations = [], int $paginateBy = DEFAULT_DATA_LIMIT): Collection|array|LengthAwarePaginator
    {
        return $this->departments->with($relations)->whereNotIn('status', [$status])->paginate($paginateBy);
    }

    public function addManager(array $data): string|object
    {
        return $this->manager->create($data);
    }
     public function updateManager(string $id, array $data): bool
    {
       return Manager::where('branch_id', $id)
            ->update($data);
    }
    public function getManager(array $params){
        return  Manager::where('branch_id', $params['branch_id'])
            ->select('name', 'phone','email')
            ->get()->first();;
    }
     public function getAdminData(array $params)
    {
       return  Admin::where('branch_id', $params['branch_id'])
            ->select('id', 'branch_id')
            ->get()->first();;
    }
    public function add(array $data): string|object
    {
        return $this->departments->create($data);
    }

    public function addDepartmentUsers(array $data): string|object
    {
        return $this->departmentUsers->create($data);
    }

     public function addToAdmin(array $data): string|object
    {
        return $this->admin->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->departments->with($relations)
            ->when(isset($params['identity']),function ($query) use ($params){
                return $query->where(['email' => $params['identity']])
                    ->orWhere(['phone' => $params['identity']]);
            })
            ->when(isset($params['id']),function ($query) use ($params){
                return $query->where(['id' => $params['id']]);
            })
            ->when(isset($params['withCount']),function ($query)use($params){
                return $query->withCount($params['withCount']);
            })
            ->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->departments->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
        $query = $this->departments->with($relations)->where($filters)
            ->when($searchValue, function ($query) use ($searchValue) {
                $searchTerms = explode(' ', $searchValue);
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) 
                    {
                        $query->orWhere('name', 'like', "%$term%");
                    }
                });
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }


    public function getUsersListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
        $directFilters = collect($filters)->except(['status', 'department_id', 'role_name'])->all();
        $query = $this->departmentUsers->with($relations)
            ->when(!empty($directFilters), function ($query) use ($directFilters) {
                $query->where($directFilters);
            })
            ->when(isset($filters['status']) && $filters['status'] === 'active', function ($query) {
                $query->active();
            })
            ->when(!empty($filters['department_id']), function ($query) use ($filters) {
                $query->inDepartment($filters['department_id']);
            })
            ->when(!empty($filters['role_name']), function ($query) use ($filters) {
                $query->withRole($filters['role_name']);
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $searchTerms = explode(' ', $searchValue);
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) 
                    {
                        $query->orWhere('name', 'like', "%$term%");
                    }
                });
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->departments->find($id)->update($data);
    }


    public function updateDepartmentUsers(string $id, array $data): bool
    {
        return $this->departmentUsers->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->departments->where($params)->delete();
        return true;
    }

    public function deleteDepartmentUsers(array $params): bool
    {
        $this->departmentUsers->where($params)->delete();
        return true;
    }
}

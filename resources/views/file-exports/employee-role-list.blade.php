<html>
@php
    use App\Support\AdminPermissionRegistry;
@endphp
<table>
    <thead>
    <tr>
        <th style="font-size: 18px">{{translate('employee_Role_List')}}</th>
    </tr>
    <tr>
        <th>{{ translate('employee_Role_Analytics') .' '.'-'}}</th>
        <th></th>
        <th>
            {{translate('search_Bar_Content')}} - {{!empty($data['searchValue']) ? $data['searchValue'] : 'N/A'}}
            <br>
            {{translate('active_Employee_Role').' '.'-'.' '.$data['active']}}
            <br>
            {{translate('inactive_Employee_Role').' '.'-'.' '.$data['inActive']}}
        </th>
    </tr>
    <tr>
        <td> {{translate('SL')}}	</td>
        <td> {{translate('role_Name')}}</td>
        <td> {{translate('Modules')}}</td>
        <td> {{translate('permissions')}}</td>
        <td> {{translate('created_At')}}</td>
        <td> {{translate('status')}}</td>
    </tr>
    @foreach ($data['roles'] as $key=>$item)
        <tr>
            <td> {{++$key}}	</td>
            <td>{{ucwords($item['name'])}}</td>
            @php
                $rolePermissionGroups = collect($item->permissions ?? [])
                    ->pluck('name')
                    ->filter(fn($permission) => str_contains((string)$permission, '.'))
                    ->mapToGroups(function ($permission) {
                        [$module, $action] = explode('.', (string)$permission, 2);
                        return [$module => $action];
                    });
            @endphp
            <td>
                @forelse($rolePermissionGroups as $module => $actions)
                    {{ AdminPermissionRegistry::moduleDisplayName((string)$module) }}<br>
                @empty
                    -
                @endforelse
            </td>
            <td>
                @forelse($rolePermissionGroups as $module => $actions)
                    @php
                        $permissionLabels = collect($actions)
                            ->unique()
                            ->values()
                            ->map(fn($action) => AdminPermissionRegistry::permissionDisplayName($module . '.' . $action))
                            ->implode(', ');
                    @endphp
                    {{ $permissionLabels }}<br>
                @empty
                    -
                @endforelse
            </td>
            <td> {{date('d M, Y h:i A',strtotime($item->created_at))}}</td>
            <td>{{translate((isset($item['status']) ? (int)$item['status'] : 1) === 1 ? 'active' : 'inactive')}}</td>
        </tr>
    @endforeach
    </thead>
</table>
</html>

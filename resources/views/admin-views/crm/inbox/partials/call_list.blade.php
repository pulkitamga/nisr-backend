@forelse($calls as $call)
@if($loop->first)
<div class="table-responsive">

<table
    style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
    <thead class="thead-light thead-50 text-capitalize">
        <tr>
            <th>{{ translate('Title') }}</th>
            <th>{{ translate('From') }}</th>
            <th>{{ translate('To') }}</th>
            <th>{{ translate('Guests') }}</th>
            <th>{{ translate('Location') }}</th>
            <th>{{ translate('Employee') }}</th>
            <th>{{ translate('Department') }}</th>
        </tr>
    </thead>
    <tbody>
        @endif
        <tr>
            <td>{{ $call->title }}</td>
            <td>{{ $call->from->format('d M, Y H:i') }}</td>
            <td>{{ $call->to->format('d M, Y H:i') }}</td>
            <td>{{ $call->guests ? \App\Models\User::find($call->guests)?->name : translate('None') }}</td>
            <td>{{ $call->location ?? translate('N/A') }}</td>
            <td>{{ $call->employee->name ?? translate('Unassigned') }}</td>
            <td>{{ $call->department?->getTranslatedField('name') ?? translate('Unassigned') }}</td>
        </tr>
        @if($loop->last)
    </tbody>
</table>
</div>
@endif
@empty
@include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
@endforelse


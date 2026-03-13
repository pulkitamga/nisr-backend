@if($files->count() > 0)
<div class="table-responsive">
    <table
        style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
        <thead class="thead-light thead-50 text-capitalize">
            <tr>
                <th>{{ translate('File') }}</th>
                <th>{{ translate('Uploaded At') }}</th>
                <th>{{ translate('Employee') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($files as $file)
            <tr>
                <td>
                    <a href="{{ Storage::url($file->file) }}" target="_blank">
                        {{ basename($file->file) }}
                    </a>
                </td>
                <td>{{ $file->created_at }}</td>
                <td>{{ $file->employee->name ?? translate('Unassigned') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
@include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
@endif


@if($notes->count() > 0)
    <div class="table-responsive">
       <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                <tr>
                    <th>{{ translate('Content') }}</th>
                    <th>{{ translate('Noted At') }}</th>
                    <th>{{ translate('Employee') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notes as $note)
                    <tr>
                        <td>{{ $note->note }}</td>
                        <td>{{ $note->noted_at }}</td>
                        <td>{{ $note->employee->name ?? 'Unassigned' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
@endif

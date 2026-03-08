@extends('layouts.back-end.app')

@section('title', translate('Career Page Sections'))
<meta name="csrf-token" content="{{ csrf_token() }}">


@section('content')

{{-- 👇 Same switch style for section toggle --}}
<style>
    .nav-link.active {
        color: #377dff;
        border-bottom: 2px solid;
    }
</style>

<div class="content container-fluid">
    {{-- 🔹 Top Section Tabs --}}
    <div class="mb-4 d-flex flex-wrap gap-3">
        @php
        $sections = [
        'current_openings' => 'Current Openings',
        'why_join_us' => 'Why Join Us',
        'perks' => 'Perks & Benefits',
        ];
        $current = request('section') ?? 'current_openings';
        @endphp

        <div class="inline-page-menu my-2">
            <ul class="list-unstyled">
                @foreach($sections as $key => $label)
                <li class="">
                    <a href="{{ route('admin.content-management.career.pages', ['section' => $key]) }}"
                        class="nav-link {{ $current == $key ? 'active' : 'text-dark' }}">
                        {{ $label }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="card">
        <div class=" flex-wrap gap-10">
            <div class="card-header px-sm-3 py-4 flex-grow-1">
                <div class="d-flex justify-content-between gap-3 flex-wrap align-items-center w-100">
                    <h5 class="mb-0 text-capitalize gap-2">
                        {{ ucfirst(str_replace('_', ' ', $current)) }} Table
                        <span class="badge badge-soft-dark radius-50 fz-12">{{ $items->total() }}</span>
                    </h5>


                    <a href="{{ route('admin.content-management.career.create', ['section' => $current]) }}"
                        class="btn btn--primary text-nowrap">
                        <i class="tio-add"></i>
                        <span class="text">{{ __('Add New') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="datatable"
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>

                        @switch($current)
                        @case('current_openings')
                        <th>{{ translate('Job Title') }}</th>
                        <th>{{ translate('Location') }}</th>
                        <th>{{ translate('Experience') }}</th>
                        <th>{{ translate('Skills') }}</th>
                        <th>{{ translate('Description') }}</th>
                        @break

                        @case('why_join_us')
                        <th>{{ translate('Title') }}</th>
                        <th>{{ translate('Description') }}</th>
                        <th>{{ translate('Icon') }}</th>
                        @break

                        @case('perks')
                        <th>{{ translate('Title') }}</th>
                        <th>{{ translate('Description') }}</th>
                        <th>{{ translate('Icon') }}</th>
                        @break

                        @case('hero')
                        <th>{{ translate('Title') }}</th>
                        <th>{{ translate('Description') }}</th>
                        <th>{{ translate('Button Text') }}</th>
                        <th>{{ translate('Button Link') }}</th>
                        <th>{{ translate('Image') }}</th>
                        @break
                        @endswitch

                        <th>{{ translate('Status') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $key => $item)
                    <tr>
                        <td>{{$key + 1}}</td>

                        @switch($current)
                        @case('current_openings')
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->location }}</td>
                        <td>{{ $item->experience }}</td>
                        <td>{{ strip_tags($item->skills) }}</td>
                        <td>{{ Str::limit(strip_tags($item->job_description), 80) }}</td>
                        @break

                        @case('why_join_us')
                        <td>{{ $item->title }}</td>
                        <td>{{ Str::limit(strip_tags($item->description), 80) }}</td>
                        <td>{{ $item->icon}}</td>
                        @break

                        @case('perks')
                        <td>{{ $item->title }}</td>
                        <td>{{ Str::limit(strip_tags($item->description), 80) }}</td>
                        <td>{{ $item->icon }}</td>
                        @break

                        @case('hero')
                        <td>{{ $item->title }}</td>
                        <td>{{ Str::limit(strip_tags($item->description), 80) }}</td>
                        <td>{{ $item->button_text }}</td>
                        <td>{{ $item->button_link }}</td>
                        <td>
                            @if($item->image)
                            <img src="{{ Storage::url($item->image) }}" style="width: 100px;">
                            @else
                          {{ translate('N/A') }}
                            @endif
                        </td>
                        @break
                        @endswitch

                        <td>
                            <label class="switcher mx-auto">
                                <input type="checkbox"
                                    class="switcher_input status-toggle"
                                    data-id="{{ $item->id }}"
                                    {{ $item->is_active ? 'checked' : '' }}>
                                <span class="switcher_control"></span>
                            </label>
                        </td>

                        <td class="text-center d-flex gap-2">
                            <a href="{{ route('admin.content-management.career.edit', ['section' => $current, 'id' => $item->id]) }}"
                                class="btn btn-outline-primary btn-sm square-btn" title="{{ translate('Edit') }}">
                                <i class="tio-edit"></i>
                            </a>

                            <form
                                action="{{ route('admin.content-management.career.destroy', ['section' => $current, 'id' => $item->id]) }}"
                                method="POST" onsubmit="return confirm('{{ translate('Delete this item?') }}');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm square-btn"
                                    title="{{ translate('Delete') }}">
                                    <i class="tio-delete"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            {{ translate('No data available for this section.') }}
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>

        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {{ $items->links() }}
            </div>
        </div>

    </div>
</div>
</div>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/jquery.js')}}"></script>

<script>
    $(document).on('change', '.status-toggle', function () {
        let $switch = $(this);
        let id = $switch.data('id');
        let section = '{{ $current }}';
        let newStatus = $switch.is(':checked') ? 1 : 0;

        // Disable switch during request
        $switch.prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.content-management.career.toggle-status') }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                section: section
            },
            success: function (response) {
                toastr.success(response.message || @json(__('Status updated successfully!')));
            },
            error: function (xhr) {
                // Revert checkbox on failure
                $switch.prop('checked', !newStatus);
                toastr.error(xhr.responseJSON?.message || @json(__('Failed to update status!')));
            },
            complete: function () {
                $switch.prop('disabled', false);
            }
        });
    });
</script>

@endsection


@extends('layouts.back-end.app')

@section('title', translate('about_page_sections'))

@section('content')

<div class="content container-fluid">
    @php
    $sections = [
    'hero' => translate('hero_section'),
    'who_we_are' => translate('who_we_are'),
    'products' => translate('products'),
    'mission' => translate('mission'),
    'timeline' => translate('timeline'),
    'dealers' => translate('dealers'),
    ];
    $current = request('section') ?? 'hero';
    @endphp

    <div class="cms-admin-heading">
        <h1 class="cms-admin-heading__title h3">{{ translate('about_page_sections') }}</h1>
    </div>

    <div class="inline-page-menu my-2">
        <ul class="list-unstyled">
            @foreach($sections as $key => $label)
            <li class="">
                <a href="{{ route('admin.content-management.about-us.pages', ['section' => $key]) }}"
                    class="nav-link {{ $current == $key ? 'active' : 'text-dark' }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>


    <div class="card">
        <div class="card-header flex-wrap gap-10">
            <div class="px-sm-3 py-4 flex-grow-1">
                <div class="d-flex justify-content-between gap-3 flex-wrap align-items-center">
                    <div>
                        <h5 class="mb-0 text-capitalize gap-2">
                            {{ $sections[$current] ?? translate($current) }}
                            <span class="badge badge-soft-dark radius-50 fz-12">{{ $items->total() }}</span>
                        </h5>
                    </div>
                    <div
                        class="align-items-center d-flex gap-3 justify-content-lg-end flex-wrap flex-lg-nowrap flex-grow-1">
                        {{-- Add New Button --}}
                        <div>
                            <a href="{{ route('admin.content-management.about-us.create', ['section' => $current]) }}"
                                class="btn btn--primary text-nowrap">
                                <i class="tio-add"></i>
                                <span class="text"> {{ translate('add_new') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
                    <thead class="thead-light text-capitalize">
                        <tr>
                            <th>{{ translate('sl') }}</th>

                            @switch($current)
                            @case('hero')
                            <th>{{ translate('heading') }}</th>
                            <th>{{ translate('subheading') }}</th>
                            <th>{{ translate('image') }}</th>
                            @break

                            @case('who_we_are')
                            <th>{{ translate('title') }}</th>
                            <th>{{ translate('content') }}</th>
                            @break

                            @case('products')
                            <th>{{ translate('title') }}</th>
                            <th>{{ translate('description') }}</th>
                            <th>{{ translate('image') }}</th>
                            @break

                            @case('mission')
                            <th>{{ translate('title') }}</th>
                            <th>{{ translate('content') }}</th>
                            @break

                            @case('timeline')
                            <th>{{ translate('year') }}</th>
                            <th>{{ translate('title') }}</th>
                            <th>{{ translate('description') }}</th>
                            <th>{{ translate('image') }}</th>
                            @break

                            @case('dealers')
                            <th>{{ translate('dealer_name') }}</th>
                            <th>{{ translate('location') }}</th>
                            <th>{{ translate('description') }}</th>
                            <th>{{ translate('image') }}</th>
                            @break
                            @endswitch

                            <th>{{ translate('status') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>

                    </thead>

                    <tbody>
                        @forelse($items as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>

                            @switch($current)
                            @case('hero')
                            <td>{{ $item->getTranslatedField('heading') ?? 'N/A' }}</td>
                            <td>{{ Str::limit($item->getTranslatedField('subheading') ?? 'N/A', 80) }}</td>
                            <td>
                                @if($item->image)
                                <img src="{{ Storage::url($item->image) }}" style="width: 100px;">
                                @else
                                {{ translate('N/A') }}
                                @endif
                            </td>
                            @break

                            @case('who_we_are')
                            <td>{{ $item->getTranslatedField('title') ?? 'N/A' }}</td>
                            <td>{{ Str::limit(strip_tags($item->getTranslatedField('content') ?? 'N/A'), 80) }}</td>
                            @break

                            @case('products')
                            <td>{{ $item->getTranslatedField('title') ?? 'N/A' }}</td>
                            <td>{{ Str::limit(strip_tags($item->getTranslatedField('description') ?? 'N/A'), 80) }}</td>
                            <td>
                                @if($item->image)
                                <img src="{{ Storage::url($item->image) }}" style="width: 100px;">
                                @else
                                {{ translate('N/A') }}
                                @endif
                            </td>
                            @break

                            @case('mission')
                            <td>{{ $item->getTranslatedField('title') ?? 'N/A' }}</td>
                            <td>{{ Str::limit(strip_tags($item->getTranslatedField('content') ?? 'N/A'), 80) }}</td>
                            @break

                            @case('timeline')
                            <td>{{ $item->getTranslatedField('year') ?? 'N/A' }}</td>
                            <td>{{ $item->getTranslatedField('title') ?? 'N/A' }}</td>
                            <td>{{ Str::limit(strip_tags($item->getTranslatedField('description') ?? 'N/A'), 80) }}</td>
                            <td> @if($item->image)
                                <img src="{{ Storage::url($item->image) }}" style="width: 60px;">
                                @else
                                {{ translate('N/A') }}
                                @endif
                            </td>
                            @break

                            @case('dealers')
                            <td>{{ $item->dealer_name ?? 'N/A' }}</td>
                            <td>{{ $item->getTranslatedField('location') ?? 'N/A' }}</td>
                            <td>{{ Str::limit(strip_tags($item->getTranslatedField('description') ?? 'N/A'), 80) }}</td>
                            <td>
                                @if($item->image)
                                <img src="{{ Storage::url($item->image) }}" style="width: 100px; height: auto;"
                                    alt="{{ translate('Image') }}">
                                @else
                                {{ translate('N/A') }}
                                @endif
                            </td>
                            @break
                            @endswitch

                            {{-- Status toggle --}}
                            <td>
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input status-toggle"
                                        data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </td>

                            {{-- Action buttons --}}
                            <td class="text-center d-flex gap-2">
                                <a href="{{ route('admin.content-management.about-us.edit', ['section' => $current, 'id' => $item->id]) }}"
                                    class="btn btn-outline-primary btn-sm square-btn" title="{{ translate('Edit') }}">
                                    <i class="tio-edit"></i>
                                </a>
                                <form id="delete-form-{{ $item->id }}"
                                    action="{{ route('admin.content-management.about-us.destroy', ['section' => $current, 'id' => $item->id]) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-outline-danger btn-sm square-btn"
                                        onclick="confirmDelete({{ $item->id }})"
                                        title="{{ translate('Delete') }}">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">{{ translate('No data available') }}</td>
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

@endsection

@push('script')
<script>
    $(document).on('change', '.status-toggle', function() {
        let id = $(this).data('id');
        let section = '{{ $current }}';

        $.ajax({
            url: "{{ route('admin.content-management.about-us.toggle-status') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                section: section
            },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error(@json(__('Something went wrong!')));
            }
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: @json(__('Are you sure?')),
            text: @json(__('You won\'t be able to revert this!')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: @json(__('Yes, delete it!')),
            cancelButtonText: @json(__('Cancel'))
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush

@extends('layouts.back-end.app')

@section('title', translate('blog_list'))

@section('content')

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }
</style>

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">

        </h2>
    </div>

    <div class="card">
        <div class="card-header flex-wrap gap-10">
            <div class="px-sm-3 py-4 flex-grow-1">
                <div class="d-flex justify-content-between gap-3 flex-wrap align-items-center">
                    <div>
                        <h5 class="mb-0 text-capitalize gap-2">
                            {{ translate('blog_table') }}
                            <span class="badge badge-soft-dark radius-50 fz-12">{{ $blogs->total() }}</span>
                        </h5>
                    </div>
                    <div
                        class="align-items-center d-flex gap-3 justify-content-lg-end flex-wrap flex-lg-nowrap flex-grow-1">
                        <div>
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-merge input-group-custom">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input type="search" name="search" class="form-control"
                                        placeholder="{{ translate('search_by_heading') }}"
                                        value="{{ request('search') }}" required>
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </div>
                            </form>
                        </div>
                        <div>
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="d-flex gap-2 align-items-center text-start">
                                    <div>
                                        <select class="form-control text-ellipsis min-w-200" name="category">
                                            <option value="all" {{ request('category')=='all' ? 'selected' : '' }}>{{
                                                translate('all') }}</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ request('category')==$category
                                                ? 'selected' : '' }}>
                                                {{ ucfirst($category) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn--primary px-4 w-100 text-nowrap">
                                            {{ translate('filter') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div>
                            <a href="{{ route('admin.content-management.blog.create') }}"
                                class="btn btn--primary text-nowrap">
                                <i class="tio-add"></i>
                                <span class="text">{{ translate('add_new') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
                    <thead class="thead-light text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('image') }}</th> {{-- 👈 Added Image Header --}}
                            <th>{{ translate('heading') }}</th>
                            <th>{{ translate('description') }}</th>
                            <th>{{ translate('category') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blogs as $key => $blog)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->getTranslatedField('heading') }}"
                                    style="width: 100px; height: auto;">
                            </td>
                            <td>{{ $blog->getTranslatedField('heading') }}</td>
                            <td>{{ $blog->getTranslatedField('description') }}</td>
                            <td>{{ $blog->getTranslatedField('category') }}</td>
                            <td>
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input status-toggle"
                                        data-id="{{ $blog->id }}" {{ $blog->status == 1 ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </td>
                            <td class="text-center d-flex gap-2">
                                <a href="{{ route('admin.content-management.blog.edit', $blog->id) }}"
                                    class="btn btn-outline-primary btn-sm square-btn" title="{{ translate('Edit') }}">
                                    <i class="tio-edit"></i>
                                </a>
                                <form action="{{ route('admin.content-management.blog.destroy', $blog->id) }}"
                                    method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('{{ translate('Are you sure you want to delete this blog?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm square-btn"
                                        title="{{ translate('Delete') }}">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>

                </table>
            </div>

            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {{ $blogs->links() }}
                </div>
            </div>

            @if(count($blogs) == 0)
            <div class="w-100">
                @include('layouts.back-end._empty-state', ['text' => 'no_blog_found'])
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).on('change', '.status-toggle', function () {
        let blogId = $(this).data('id');

        $.ajax({
            url: "{{ route('admin.content-management.blog.status', '') }}/" + blogId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                toastr.success(response.message);
            },
            error: function () {
                toastr.error(@json(__('Something went wrong!')));
            }
        });
    });
</script>
@endpush

@extends('layouts.back-end.app')

@section('title', translate('home_page'))
@push('css')

@endpush
@section('content')
<style>
    .nav-link.active {
        color: #1455ac;
        border-bottom: 2px solid;
    }
</style>
<section>
    <div class="content container-fluid">


        <div class="inline-page-menu my-4">
            <div class="overflow-auto" style="white-space: nowrap;">
                <ul class="list-unstyled d-inline-flex gap-2 mb-2">
                    @foreach($typeList as $key => $label)
                    <li class="me-2 d-inline-block">
                        <a href="{{ route('admin.content-management.home', ['section' => $key]) }}"
                            class="nav-link {{ $currentType == $key ? 'active' : 'text-dark' }}">
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">

                <h5 class="mb-0 text-capitalize">{{ $typeList[$currentType] ?? $currentType }}</h5>

                <div class="form-group d-flex align-items-center gap-3">

                    <label class="switcher mx-auto">
                        <input type="checkbox" class="switcher_input section-toggle" data-type="{{ $currentType }}" {{
                            $currentSection->is_active ? 'checked' : '' }}>
                        <span class="switcher_control"></span>
                    </label>



                </div>
            </div>



            <div class="card-body p-3">
                @if (in_array($currentType, ['main_banner'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.main-banner')
                @endif

                @if (in_array($currentType, ['client_review'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.clint-review')
                @endif

                @if (in_array($currentType, ['categories'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.deals')
                @endif

                @if (in_array($currentType, ['find_perfect_match'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.find-match')
                @endif

                @if (in_array($currentType, ['download_app'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.download-mobile')
                @endif

                @if (in_array($currentType, ['products'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.products')
                @endif
                @if (in_array($currentType, ['blog'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.blog')
                @endif

                @if (in_array($currentType, ['trusted_by'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.trusted-by')
                @endif

                @if (in_array($currentType, ['wholesaler_section'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.wholesaler')
                @endif

                @if (in_array($currentType, ['why_choose_us'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.why-choose-us')
                @endif

                @if (in_array($currentType, ['why_join_us'])) {{-- You can add more types here --}}
                @include('admin-views.content-management.home.partials.why-join-us')
                @endif

            </div>
        </div>
    </div>
</section>


@endsection

@push('script')
<script src="https://fitandfix.guptatechweb.com/assets/back-end/js/sweet_alert.js"></script>



<script>
    $(document).ready(function () {
        $(document).on('change', '.section-toggle', function () {
            let type = $(this).data('type');
            let status = $(this).is(':checked') ? 1 : 0;

            console.log("Toggle clicked", type, status); // ✅ Now inside scope

            $.ajax({
                url: "{{ route('admin.content-management.section.toggle-status') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    type: type,
                    status: status
                },
                success: function (response) {
                    toastr.success('Status updated successfully');
                },
                error: function () {
                    toastr.error('Failed to update status');
                }
            });
        });
    });
</script>
@endpush
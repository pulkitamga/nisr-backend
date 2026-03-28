@extends('layouts.back-end.app')

@section('title', translate('sub_Sub_Category'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/brand-setup.png') }}" alt="">
            {{ translate('sub_Sub_Category_Setup') }}
        </h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-start">
                    <form action="{{ route('admin.sub-sub-category.store') }}" method="POST">
                        @csrf
                        @php
                            $activeLanguage = in_array(getDefaultLanguage(), $language ?? $languages ?? [], true)
                                ? getDefaultLanguage()
                                : $defaultLanguage;
                        @endphp
                        <ul class="nav nav-tabs w-fit-content mb-4">
                            @foreach($languages as $lang)
                            <li class="nav-item text-capitalize">
                                <span class="nav-link form-system-language-tab cursor-pointer {{ $lang == $activeLanguage ? 'active' : '' }}" id="{{ $lang}}-link">{{ucfirst(getLanguageName($lang)).'('.strtoupper($lang).')'}}</span>
                            </li>
                            @endforeach
                        </ul>
                        <div class="row">
                            @foreach($languages as $lang)
                            <div class="col-12 form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form" id="{{ $lang}}-form">
                                <label class="title-color" for="exampleFormControlInput1">{{ translate('sub_sub_category_name') }}
                                    <span class="text-danger">*</span>
                                    ({{strtoupper($lang) }})</label>
                                <input type="text" name="name[]" class="form-control" placeholder="{{ translate('new_Sub_Sub_Category') }}">
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $lang}}">
                            @endforeach
                            <input name="position" value="2" class="d-none">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="title-color">{{ translate('main_Category') }}
                                        <span class="text-danger">*</span></label>
                                    <select class="form-control action-get-sub-category-onchange" id="main-category" required data-route="{{ route('admin.sub-sub-category.getSubCategory') }}">
                                        <option value="" disabled selected>{{ translate('select_main_category') }}</option>
                                        @foreach($parentCategories as $category)
                                        <option value="{{ $category['id']}}">{{ $category['defaultName']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="title-color text-capitalize" for="name">
                                        {{ translate('sub_category_Name') }}<span class="text-danger">*</span>
                                    </label>
                                    <select name="parent_id" id="parent_id" class="form-control">
                                        <option value="" disabled selected>
                                            {{ translate('select_sub_category_first') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="title-color text-capitalize" for="priority">
                                        {{ translate('priority') }}
                                        <span>
                                            <i class="tio-info-outined" data-toggle="tooltip" data-placement="top" title="{{ translate('the_lowest_number_will_get_the_highest_priority') }}"></i>
                                        </span>
                                    </label>
                                    <select class="form-control" name="priority" id="" required>
                                        <option disabled selected>{{ translate('set_Priority') }}</option>
                                        @for ($increment = 0; $increment <= 10; $increment++) <option value="{{ $increment }}">{{ $increment }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="{{ theme_root_path() == 'theme_aster' ? 'w-100' : 'col-md-6 col-lg-4' }}">
                                <label class="title-color" for="exchange_charge">{{ translate('product_exchange_charge') }}</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="exchange_charge" placeholder="e.g. 50">
                            </div>

                            <div class="{{ theme_root_path() == 'theme_aster' ? 'w-100' : 'col-md-6 col-lg-4' }}">
                                <label class="title-color" for="installation_charge">{{ translate('product_installation_charge') }}</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="installation_charge" placeholder="e.g. 100">
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <button type="reset" class="btn btn-secondary">
                                        {{ translate('reset') }}
                                    </button>
                                    <button type="submit" class="btn btn--primary">
                                        {{ translate('submit') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-20" id="cate-table">
        <div class="col-md-12">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                        <div class="">
                            <h5 class="text-capitalize d-flex gap-1">
                                {{ translate('sub_sub_category_list') }}
                                <span class="badge badge-soft-dark radius-50 fz-12">{{ $categories->total() }}</span>
                            </h5>
                        </div>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-custom input-group-merge">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="" type="search" name="searchValue" class="form-control" placeholder="{{ translate('search_by_sub_sub_category_name') }}" value="{{ request('searchValue') }}">
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </div>
                            </form>
                            <div class="dropdown">
                                <a type="button" class="btn btn-outline--primary text-nowrap" href="{{ route('admin.sub-sub-category.export',['searchValue'=>request('searchValue')]) }}">
                                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                    <span class="ps-2">{{ translate('export') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('ID') }}</th>
                                <th>{{ translate('sub_sub_category_name') }}</th>
                                <th>{{ translate('sub_category_name') }}</th>
                                <th>{{ translate('category_name') }}</th>
                                <th class="text-center">{{ translate('priority') }}</th>
                                <th class="text-center">{{ translate('exchange_charge') }}</th>
                                <th class="text-center">{{ translate('installation_charge') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $key=>$category)
                            <tr>
                                <td>{{ $category['id']}}</td>
                                <td>{{ $category['defaultName']}}</td>
                                <td>{{$category?->parent?->defaultname ?? translate('sub_category_not_found') }}</td>
                                <td>{{$category?->parent?->parent?->defaultname ??translate('sub_category_not_found') }}</td>
                                <td class="text-center">{{ $category['priority']}}</td>
                                <td class="text-center">
                                    @php
                                    $exchangeCharge = $charges->firstWhere(function($charge) use ($category) {
                                    return $charge->category_id == $category->id && $charge->type == 'exchange';
                                    });
                                    @endphp

                                    @if($exchangeCharge)
                                    <span>{{ $exchangeCharge->charges }}</span>
                                    <input type="checkbox" class="charge-status" data-id="{{ $exchangeCharge->id }}" data-type="exchange" {{ $exchangeCharge->status ? 'checked' : '' }}>
                                    @else
                                    <span>—</span>
                                    @endif
                                </td>

                                <!-- Displaying the installation charge with a checkbox -->
                                <td class="text-center">
                                    @php
                                    $installationCharge = $charges->firstWhere(function($charge) use ($category) {
                                    return $charge->category_id == $category->id && $charge->type == 'installation';
                                    });
                                    @endphp

                                    @if($installationCharge)
                                    <span>{{ $installationCharge->charges }}</span>
                                    <input type="checkbox" class="charge-status" data-id="{{ $installationCharge->id }}" data-type="installation" {{ $installationCharge->status ? 'checked' : '' }}>
                                    @else
                                    <span>—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="btn btn-outline-info btn-sm square-btn" title="{{ translate('edit') }}" href="{{ route('admin.sub-sub-category.update',[$category['id']]) }}">
                                            <i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-outline-danger btn-sm square-btn category-delete-button" title="{{ translate('delete') }}" id="{{ $category['id']}}">
                                            <i class="tio-delete"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <div class="d-flex justify-content-lg-end">
                        {{ $categories->links() }}
                    </div>
                </div>

                @if(count($categories)==0)
                @include('layouts.back-end._empty-state',['text'=>'no_sub_sub_category_found'],['image'=>'default'])
                @endif
            </div>
        </div>
    </div>
</div>

<span id="route-admin-category-delete" data-url="{{ route('admin.sub-sub-category.delete') }}"></span>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/products-management.js') }}"></script>
<script>
    $(document).ready(function() {
        // Listen for checkbox change event
        $('.charge-status').change(function() {
            let chargeId = $(this).data('id');
            let chargeType = $(this).data('type');
            let newStatus = $(this).prop('checked') ? 1 : 0;

            // Send the status update to the backend
            $.ajax({
                url: '{{ route("admin.sub-category.updateExtraChargeStatus") }}', // The route to update status
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}'
                    , id: chargeId
                    , type: chargeType
                    , status: newStatus
                }
                , success: function(response) {
                    if (response.success) {
                        toastr.success(response.message); // Success message
                    } else {
                        toastr.error(response.message); // Error message
                        // Optionally, revert the checkbox state if update fails
                        $(this).prop('checked', !newStatus);
                    }
                }
                , error: function() {
                    toastr.error(@json(__('Failed to update status')));
                    // Optionally, revert the checkbox state if update fails
                    $(this).prop('checked', !newStatus);
                }
            });
        });
    });

</script>
@endpush


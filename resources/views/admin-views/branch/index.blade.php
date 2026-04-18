@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Branch_List'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
@php
    $toolbarFields = [
        [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'placeholder' => translate('Ex') . ' : 200',
            'col_class' => 'col-xl-2 col-lg-4',
            'attributes' => ['min' => '1'],
        ],
        [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('Search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_branch_name_or_phone_or_email'),
            'aria_label' => translate('search_by_branch_name_or_phone_or_email'),
            'col_class' => 'col-xl-5 col-lg-8',
        ],
    ];

    $toolbarSummary = [];
    if (request()->filled('searchValue')) {
        $toolbarSummary[] = [
            'label' => translate('Search'),
            'value' => Str::limit(request('searchValue'), 28),
            'muted' => true,
        ];
    }
    if (request()->filled('choose_first')) {
        $toolbarSummary[] = [
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'muted' => true,
        ];
    }

    $headerActions = [
        [
            'type' => 'export',
            'url' => route('admin.branch.export'),
            'form_id' => 'branch-list-toolbar',
            'label' => translate('export'),
        ],
        [
            'type' => 'button',
            'label' => translate('add_New_Branch'),
            'href' => route('admin.branch.add'),
            'class' => 'btn btn--primary text-nowrap',
            'icon_html' => '<i class="tio-add"></i>',
        ],
    ];
@endphp
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
            {{ translate('Branch_List') }}
            <span class="badge badge-soft-dark radius-50 fz-12">{{ $branches->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'branch-list-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.branch.branch-list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Branch_List'),
            'listHeaderTotal' => $branches->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('Branch_Name') }}</th>
                        <th>{{ translate('branch_manager') }}</th>
                        <th>{{ translate('Branch_Address') }}</th>
                        <th>{{ translate('branch_Zipcode') }}</th>
                        <th>{{ translate('_contact_info') }}</th>
                        <th>{{ translate('Shipping_area') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $key => $seller)
                        <tr>
                            <td>{{ $branches->firstItem() + $key }}</td>
                            <td>
                                <a href="{{ route('admin.branch.view', $seller->id) }}" class="crm-primary-link">
                                    {{ $seller->getTranslatedField('branch_name') }}
                                </a>
                            </td>
                            <td>
                                @if($seller->manager)
                                    {{ $seller->manager->name }}
                                @else
                                    <span class="text-muted">{{ translate('not_assigned') }}</span>
                                @endif
                            </td>
                            <td>{{ $seller->getTranslatedField('branch_country') }}, {{ $seller->getTranslatedField('branch_address') }}</td>
                            <td>{{ $seller->branch_zipcode }}</td>
                            <td>
                                <div class="mb-1">
                                    <strong><a class="title-color hover-c1" href="mailto:{{ $seller->email }}">{{ $seller->email }}</a></strong>
                                </div>
                                <a class="title-color hover-c1" href="tel:{{ $seller->phone }}">{{ $seller->phone }}</a>
                            </td>
                            <td>{{ $seller->shipping_method_areas }}</td>
                            <td>
                                {!! $seller->status == 'active'
                                    ? '<label class="badge badge-success">' . translate('Active') . '</label>'
                                    : '<label class="badge badge-danger">' . translate('Inactive') . '</label>' !!}
                            </td>
                            <td class="text-center">
                                @if($seller->id != 1)
                                    <div class="crm-row-actions">
                                        <div class="crm-row-actions__primary">
                                            <a title="{{ translate('View') }}" class="btn btn-outline-info btn-sm" href="{{ route('admin.branch.view', $seller->id) }}">
                                                {{ translate('View') }}
                                            </a>
                                            <a title="{{ translate('Edit') }}" class="btn btn-outline--primary btn-sm" href="{{ route('admin.branch.update', $seller->id) }}">
                                                {{ translate('Edit') }}
                                            </a>
                                        </div>
                                        <div class="dropdown crm-row-actions__menu">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                                <i class="tio-more-horizontal"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <form action="{{ route('admin.branch.chose.delete', $seller->id) }}" method="POST" class="crm-row-actions__form" onsubmit="return confirm('{{ translate('Are you sure you want to delete this branch?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="tio-delete-outlined mr-2"></i>{{ translate('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ translate('No data available') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-center justify-content-md-end">
                {!! $branches->links() !!}
            </div>
        </div>

        @if(count($branches) == 0)
            @include('layouts.back-end._empty-state', ['text' => 'no_vendor_found'], ['image' => 'default'])
        @endif
    </div>
</div>
@endsection

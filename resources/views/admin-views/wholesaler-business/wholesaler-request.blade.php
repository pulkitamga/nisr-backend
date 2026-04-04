@extends('layouts.back-end.app')

@section('title', translate('WholeSaler_Business_Requests'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/wholesale-list.css') }}">
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="content container-fluid">
    @php
        $toolbarFields = [
            [
                'type' => 'search',
                'name' => 'searchValue',
                'label' => translate('search'),
                'value' => request('searchValue'),
                'placeholder' => translate('search_by_Name_or_Email_or_Phone'),
                'aria_label' => translate('search_by_Name_or_Email_or_Phone'),
                'col_class' => 'col-xl-8 col-lg-8',
            ],
            [
                'type' => 'number',
                'name' => 'choose_first',
                'label' => translate('Rows_to_show'),
                'value' => request('choose_first'),
                'placeholder' => translate('Ex') . ' : 50',
                'col_class' => 'col-xl-4 col-lg-4',
                'attributes' => ['min' => '1'],
            ],
        ];
        $toolbarSummary = [
            [
                'label' => translate('Status'),
                'value' => translate('Wholesaler_request'),
            ],
        ];
        if (request()->filled('searchValue')) {
            $toolbarSummary[] = [
                'label' => translate('search'),
                'value' => \Illuminate\Support\Str::limit(request('searchValue'), 28),
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
                'url' => route('admin.wholesale.business.wholesale-req.export'),
                'form_id' => 'wholesale-request-toolbar',
                'label' => translate('export'),
            ],
        ];
    @endphp

    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
            {{ translate('WholeSaler_Business_Requests') }}
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'wholesale-request-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.wholesale.business.request'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Wholesaler_request'),
            'listHeaderTotal' => $wholesaler_business->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="px-3 py-4">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('wholesaler') }}</th>
                            <th>{{ translate('company') }}</th>
                            <th>{{ translate('trade') }}</th>
                            <th>{{ translate('reg._no.') }}</th>
                            <th>{{ translate('tax._no.') }}</th>
                            <th>{{ translate('VAT._no.') }}</th>
                            <th>{{ translate('Approval setup') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wholesaler_business as $key => $business)
                            @php
                                $isActiveApprovalModal = (string) old('id') === (string) ($business->wholesaler['id'] ?? '');
                                $selectedTier = $isActiveApprovalModal ? old('tier') : $business->wholesaler->tier;
                                $selectedDiscount = $isActiveApprovalModal ? old('wholesaler_discount') : $business->wholesaler->wholesaler_discount;
                                $tierLabel = optional($tiers->firstWhere('name', $business->wholesaler->tier))->getTranslatedField('name');
                                $documentCards = [
                                    [
                                        'label' => translate('Registration copy'),
                                        'file' => $business->register_copy,
                                        'path' => 'storage/register_copies/',
                                    ],
                                    [
                                        'label' => translate('Tax card copy'),
                                        'file' => $business->tax_card_copy,
                                        'path' => 'storage/tax_cards/',
                                    ],
                                    [
                                        'label' => translate('VAT registration copy'),
                                        'file' => $business->vat_register_copy,
                                        'path' => 'storage/vat_copies/',
                                    ],
                                ];
                                $availableDocumentCount = collect($documentCards)->filter(fn ($document) => filled($document['file']))->count();
                            @endphp
                            <tr>
                                <td>{{ $wholesaler_business->firstItem() + $key }}</td>
                                <td><span class="bidi-auto">{{ $business->wholesaler->name ?? translate('N/A') }}</span></td>
                                <td>
                                    <a href="{{ route('admin.wholesale.business.wholesaler.profile', $business->id) }}" class="crm-primary-link bidi-auto">
                                        {{ $business->company_name ?? translate('N/A') }}
                                    </a>
                                </td>
                                <td><span class="bidi-auto">{{ $business->trade_name ?? translate('N/A') }}</span></td>
                                <td>
                                    <span class="bidi-ltr">{{ $business->registration_number ?? translate('N/A') }}</span>
                                </td>
                                <td>
                                    <span class="bidi-ltr">{{ $business->tax_id ?? translate('N/A') }}</span>
                                </td>
                                <td>
                                    <span class="bidi-ltr">{{ $business->vat_number ?? translate('N/A') }}</span>
                                </td>
                                <td>
                                    <div class="crm-row-actions__chips justify-content-start">
                                        @if($business->wholesaler->tier)
                                            <span class="crm-row-actions__chip">{{ $tierLabel ?: $business->wholesaler->tier }}</span>
                                        @else
                                            <span class="crm-row-actions__chip">{{ translate('Pending setup') }}</span>
                                        @endif
                                        <span class="crm-row-actions__chip">{{ (float) ($business->wholesaler->wholesaler_discount ?? 0) }}%</span>
                                        <span class="crm-row-actions__chip">{{ $availableDocumentCount }} {{ translate('documents') }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="crm-row-actions">
                                        <div class="crm-row-actions__primary">
                                            <a class="btn btn-sm btn-info" href="{{ route('admin.wholesale.business.wholesaler.profile', $business->id) }}">
                                                {{ translate('view') }}
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#approvalReviewModal{{ $business->wholesaler['id'] }}"
                                                    data-toggle="modal"
                                                    data-target="#approvalReviewModal{{ $business->wholesaler['id'] }}">
                                                {{ translate('Review') }}
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade"
                                 id="approvalReviewModal{{ $business->wholesaler['id'] }}"
                                 tabindex="-1"
                                 role="dialog"
                                 aria-labelledby="approvalReviewModalLabel{{ $business->wholesaler['id'] }}"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                    <div class="modal-content text-start wholesale-review-modal" data-wholesaler-approval-modal="{{ $business->wholesaler['id'] }}">
                                        <div class="modal-header wholesale-review-modal__header">
                                            <div class="wholesale-review-modal__hero">
                                                <p class="wholesale-review-modal__eyebrow mb-2">{{ translate('Review') }}</p>
                                                <h5 class="modal-title wholesale-review-modal__title mb-2" id="approvalReviewModalLabel{{ $business->wholesaler['id'] }}">
                                                    <span class="bidi-auto">{{ $business->company_name ?? translate('N/A') }}</span>
                                                </h5>
                                                <div class="wholesale-review-modal__meta">
                                                    <span class="wholesale-review-modal__meta-pill">
                                                        <span class="wholesale-review-modal__meta-label">{{ translate('wholesaler') }}</span>
                                                        <span class="bidi-auto">{{ $business->wholesaler->name ?? translate('N/A') }}</span>
                                                    </span>
                                                    <span class="wholesale-review-modal__meta-pill">
                                                        <span class="wholesale-review-modal__meta-label">{{ translate('trade') }}</span>
                                                        <span class="bidi-auto">{{ $business->trade_name ?? translate('N/A') }}</span>
                                                    </span>
                                                    <span class="wholesale-review-modal__meta-pill wholesale-review-modal__meta-pill--accent">
                                                        <span class="wholesale-review-modal__meta-label">{{ translate('documents') }}</span>
                                                        <span>{{ $availableDocumentCount }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body wholesale-review-modal__body">
                                            <div class="row g-4">
                                                <div class="col-xl-4">
                                                    <section class="wholesale-review-panel wholesale-review-panel--summary h-100">
                                                        <div class="wholesale-review-panel__header">
                                                            <h6 class="mb-0">{{ translate('Business summary') }}</h6>
                                                        </div>

                                                        <div class="wholesale-review-facts">
                                                            <div class="wholesale-review-fact">
                                                                <span class="wholesale-review-fact__label">{{ translate('reg._no.') }}</span>
                                                                <span class="wholesale-review-fact__value bidi-ltr">{{ $business->registration_number ?? translate('N/A') }}</span>
                                                            </div>
                                                            <div class="wholesale-review-fact">
                                                                <span class="wholesale-review-fact__label">{{ translate('tax._no.') }}</span>
                                                                <span class="wholesale-review-fact__value bidi-ltr">{{ $business->tax_id ?? translate('N/A') }}</span>
                                                            </div>
                                                            <div class="wholesale-review-fact">
                                                                <span class="wholesale-review-fact__label">{{ translate('VAT._no.') }}</span>
                                                                <span class="wholesale-review-fact__value bidi-ltr">{{ $business->vat_number ?? translate('N/A') }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="wholesale-review-status">
                                                            <div class="wholesale-review-status__item">
                                                                <span class="wholesale-review-status__label">{{ translate('tier') }}</span>
                                                                <span class="wholesale-review-status__value">
                                                                    {{ $business->wholesaler->tier ? ($tierLabel ?: $business->wholesaler->tier) : translate('Pending setup') }}
                                                                </span>
                                                            </div>
                                                            <div class="wholesale-review-status__item">
                                                                <span class="wholesale-review-status__label">{{ translate('discount %') }}</span>
                                                                <span class="wholesale-review-status__value">{{ (float) ($business->wholesaler->wholesaler_discount ?? 0) }}%</span>
                                                            </div>
                                                        </div>
                                                    </section>
                                                </div>
                                                <div class="col-xl-8">
                                                    <section class="wholesale-review-panel h-100">
                                                        <div class="wholesale-review-panel__header">
                                                            <h6 class="mb-0">{{ translate('Approval setup') }}</h6>
                                                        </div>
                                                        <form id="approvalApproveForm{{ $business->wholesaler['id'] }}"
                                                              action="{{ route('admin.wholesale.business.approve-reject') }}"
                                                              method="POST"
                                                              class="wholesale-review-form">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $business->wholesaler['id'] }}">
                                                            <input type="hidden" name="action" value="approve">

                                                            <div class="row g-3">
                                                                <div class="col-md-7">
                                                                    <label class="form-label wholesale-review-form__label" for="approval-tier-{{ $business->wholesaler['id'] }}">{{ translate('tier') }}</label>
                                                                    <select name="tier"
                                                                            id="approval-tier-{{ $business->wholesaler['id'] }}"
                                                                            class="form-control wholesale-review-form__control {{ $isActiveApprovalModal && $errors->has('tier') ? 'is-invalid' : '' }}"
                                                                            required>
                                                                        <option value="" disabled {{ blank($selectedTier) ? 'selected' : '' }}>
                                                                            {{ translate('Select Tier') }}
                                                                        </option>
                                                                        @foreach($tiers as $tier)
                                                                            <option value="{{ $tier->name }}" {{ (string) $selectedTier === (string) $tier->name ? 'selected' : '' }}>
                                                                                {{ $tier->getTranslatedField('name') }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @if($isActiveApprovalModal && $errors->has('tier'))
                                                                        <div class="invalid-feedback d-block">{{ $errors->first('tier') }}</div>
                                                                    @endif
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <label class="form-label wholesale-review-form__label" for="approval-discount-{{ $business->wholesaler['id'] }}">{{ translate('discount %') }}</label>
                                                                    <input type="number"
                                                                           name="wholesaler_discount"
                                                                           id="approval-discount-{{ $business->wholesaler['id'] }}"
                                                                           class="form-control wholesale-review-form__control {{ $isActiveApprovalModal && $errors->has('wholesaler_discount') ? 'is-invalid' : '' }}"
                                                                           min="0"
                                                                           step="0.01"
                                                                           value="{{ $selectedDiscount }}"
                                                                           required>
                                                                    @if($isActiveApprovalModal && $errors->has('wholesaler_discount'))
                                                                        <div class="invalid-feedback d-block">{{ $errors->first('wholesaler_discount') }}</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </section>
                                                </div>
                                            </div>

                                            <section class="wholesale-review-documents mt-4">
                                                <div class="wholesale-review-panel__header wholesale-review-panel__header--documents">
                                                    <div>
                                                        <h6 class="mb-1">{{ translate('Available documents') }}</h6>
                                                        <div class="wholesale-review-modal__meta">
                                                            @foreach($documentCards as $document)
                                                                <span class="wholesale-review-modal__meta-pill {{ blank($document['file']) ? 'wholesale-review-modal__meta-pill--muted' : '' }}">
                                                                    {{ $document['label'] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    @foreach($documentCards as $document)
                                                        <div class="col-md-4">
                                                            <article class="wholesale-document-card h-100">
                                                                <div class="wholesale-document-card__header">
                                                                    <h6 class="mb-0">{{ $document['label'] }}</h6>
                                                                    @if(filled($document['file']))
                                                                        <a href="{{ asset($document['path'] . $document['file']) }}"
                                                                           target="_blank"
                                                                           rel="noopener noreferrer"
                                                                           class="btn btn-outline-info btn-sm wholesale-document-card__preview">
                                                                            {{ translate('Preview') }}
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                                @if(filled($document['file']))
                                                                    <a href="{{ asset($document['path'] . $document['file']) }}"
                                                                       target="_blank"
                                                                       rel="noopener noreferrer"
                                                                       class="wholesale-document-card__image-link">
                                                                        <img src="{{ asset($document['path'] . $document['file']) }}"
                                                                             class="img-fluid wholesale-document-card__image"
                                                                             alt="{{ $document['label'] }}"
                                                                             loading="lazy">
                                                                    </a>
                                                                @else
                                                                    <div class="wholesale-document-card__empty">
                                                                        {{ translate('No document uploaded') }}
                                                                    </div>
                                                                @endif
                                                            </article>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </section>
                                        </div>
                                        <div class="modal-footer wholesale-review-modal__footer">
                                            <a href="{{ route('admin.wholesale.business.wholesaler.profile', $business->id) }}" class="btn btn-secondary">
                                                {{ translate('view') }}
                                            </a>
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" data-bs-dismiss="modal">
                                                    {{ translate('Close') }}
                                                </button>
                                                <form id="approvalRejectForm{{ $business->wholesaler['id'] }}"
                                                      action="{{ route('admin.wholesale.business.approve-reject') }}"
                                                      method="POST"
                                                      class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $business->wholesaler['id'] }}">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        {{ translate('Reject') }}
                                                    </button>
                                                </form>
                                                <button type="submit" form="approvalApproveForm{{ $business->wholesaler['id'] }}" class="btn btn-success">
                                                    {{ translate('Approve') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-danger py-4">
                                    {{ translate('No wholesale requests found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-center justify-content-md-end">
                {{ $wholesaler_business->links() }}
            </div>
        </div>
    </div>
</div>

@push('script_2')
@include('admin-views.wholesaler-business.partials._list-js-config', [
    'reopenApprovalModalId' => old('id'),
])
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/wholesale-list.js') }}"></script>
@endpush

@endsection

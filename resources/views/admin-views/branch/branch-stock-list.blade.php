 @php use Illuminate\Support\Str; @endphp
 @extends('layouts.back-end.app')

 @section('title', translate('branch_stocks'))

 @section('content')
     {{-- <div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
 {{translate('branch_Stocks')}}
 <span class="badge badge-soft-dark radius-50 fz-12">{{ $branches->total() }}</span>
 </h2>
 </div>
 <div class="card mb-3">
     <div class="card-body">
         <form action="{{ url()->current() }}" method="GET">
             <div class="row g-3 align-items-end">
                 <div class="col-md-4">
                     <label class="form-label">{{ translate('Branch') }}</label>
                     <select name="branch_id" class="form-control">
                         <option value="">{{ translate('All') }}</option>
                         @foreach ($branchList as $id => $name)
                         <option value="{{ $id }}" {{ request('branch_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                         @endforeach
                     </select>
                 </div>

                 <div class="col-md-4">
                     <label class="form-label">{{ translate('Product') }}</label>
                     <select name="product_id" class="form-control">
                         <option value="">{{ translate('All') }}</option>
                         @foreach ($productList as $id => $name)
                         <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                         @endforeach
                     </select>
                 </div>

                 <div class="col-md-4">
                     <label class="form-label">{{ translate('Attribute') }}</label>
                     <input type="text" name="attribute" class="form-control" value="{{ request('attribute') }}">
                 </div>

                 <div class="col-md-12 d-flex justify-content-end gap-4">
                     <button type="submit" class="btn btn--primary"> <i class="tio-filter-list"></i> {{ translate('Filter') }}
                     </button>
                     <a href="{{ url()->current() }}" class="btn btn-secondary">{{ translate('Reset') }}</a>
                 </div>
             </div>
         </form>
     </div>
 </div>

 <div class="row mt-4">
     <div class="col-md-12">
         <div class="card">
             <div class="px-3 py-4">
                 <div class="d-flex justify-content-between gap-10 flex-wrap align-items-center">
                     <div class="">
                         <form action="{{ url()->current() }}" method="GET">
                             <div class="input-group input-group-merge input-group-custom width-500px">
                                 <div class="input-group-prepend">
                                     <div class="input-group-text">
                                         <i class="tio-search"></i>
                                     </div>
                                 </div>
                                 <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                     placeholder="{{translate('search_by_branch_name_or_product_name')}}" aria-label="Search orders" value="{{ request('searchValue') }}">
                                 <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                             </div>
                         </form>
                     </div>
                     <div class="d-flex justify-content-end gap-2">
                         <div class="dropdown">
                             <a type="button" class="btn btn-outline--primary text-nowrap btn-block"
                                 href="{{ route('admin.branch.export', request()->only(['searchValue', 'branch_id', 'product_id', 'attribute'])) }}">
                                 <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                 <span class="ps-2">{{ translate('export') }}</span>
                             </a>

                         </div>

                         <a href="{{route('admin.branch.add')}}" type="button" class="btn btn--primary text-nowrap">
                             <i class="tio-add"></i>
                             {{translate('add_New_Branch')}}
                         </a>
                     </div>
                 </div>
             </div>
             <div class="table-responsive">
                 <table
                     style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                     class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                     <thead class="thead-light thead-50 text-capitalize">
                         <tr>
                             <th class="text-center">{{translate('SL')}}</th>

                             <th>{{translate('branch_name')}}</th>
                             <th>{{translate('product_name')}}</th>
                             <th>{{translate('variation')}}</th>

                             <th class="text-center">{{translate('Current_stock')}}</th>
                         </tr>
                     </thead>
                     <tbody>
                         @forelse($branches as $key => $stock)
                         <tr>
                             <td class="text-center">{{ $key + 1 + ($branches->currentPage() - 1) * $branches->perPage() }}</td>
                             <td>{{ $stock->branch->branch_name ?? translate('N/A') }}</td>
                             <td>{{ $stock->product->name ?? translate('N/A') }}</td>
                             <td>
                                 @if ($stock->variation_type)
                                 <div class="d-flex align-items-center gap-2">
                                     <span class="badge badge-soft-primary">{{ $stock->variation_type }}</span>
                                     @if ($stock->variation_key)
                                     <small class="text-muted">
                                         ({{ Str::replace('|', ' • ', Str::replace(':', ' : ', $stock->variation_key)) }})
                                     </small>
                                     @endif
                                 </div>
                                 @else
                                 <span class="badge badge-soft-dark"></span>
                                 @endif
                             </td>
                             <td class="text-center">{{ $stock->total_stock }}</td>
                         </tr>
                         @empty
                         <tr>
                             <td colspan="5" class="text-center">{{ translate('No data available') }}</td>
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
             @if (count($branches) == 0)
             @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
             @endif
         </div>
     </div>
 </div>
 </div> --}}
     <div class="content container-fluid">
         <div class="mb-4">
             <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                 <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
                 {{ translate('branch_Stocks') }}
                 <span class="badge badge-soft-dark radius-50 fz-12">{{ $branches->total() }}</span>
             </h2>
         </div>
         <div class="card mb-3">
             <div class="card-body">
                 <form action="{{ url()->current() }}" method="GET">
                     <div class="row g-3 align-items-end">
                         <div class="col-md-4">
                             <label class="form-label">{{ translate('Branch') }}</label>
                             <select name="branch_id" class="form-control">
                                 <option value="">{{ translate('All') }}</option>
                                 @foreach ($branchList as $id => $name)
                                     <option value="{{ $id }}"
                                         {{ request('branch_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                 @endforeach
                             </select>
                         </div>

                         <div class="col-md-4">
                             <label class="form-label">{{ translate('Product') }}</label>
                             <select name="product_id" class="form-control">
                                 <option value="">{{ translate('All') }}</option>
                                 @foreach ($productList as $id => $name)
                                     <option value="{{ $id }}"
                                         {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                 @endforeach
                             </select>
                         </div>

                         <div class="col-md-4">
                             <label class="form-label">{{ translate('Attribute') }}</label>
                             <input type="text" name="attribute" class="form-control"
                                 value="{{ request('attribute') }}">
                         </div>

                         <div class="col-md-12 d-flex justify-content-end gap-4">
                             <button type="submit" class="btn btn--primary"> <i class="tio-filter-list"></i>
                                 {{ translate('Filter') }}
                             </button>
                             <a href="{{ url()->current() }}" class="btn btn-secondary">{{ translate('Reset') }}</a>
                         </div>
                     </div>
                 </form>
             </div>
         </div>

         <div class="row mt-4">
             <div class="col-md-12">
                 <div class="card">
                     <div class="px-3 py-4">
                         <div class="d-flex justify-content-between gap-10 flex-wrap align-items-center">
                             <div class="">
                                 <form action="{{ url()->current() }}" method="GET">
                                     <div class="input-group input-group-merge input-group-custom width-500px">
                                         <div class="input-group-prepend">
                                             <div class="input-group-text">
                                                 <i class="tio-search"></i>
                                             </div>
                                         </div>
                                         <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                             placeholder="{{ translate('search_by_branch_name_or_product_name') }}"
                                             aria-label="{{ translate('Search orders') }}"
                                             value="{{ request('searchValue') }}">
                                         <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                     </div>
                                 </form>
                             </div>
                             <div class="d-flex justify-content-end gap-2">
                                 <div class="dropdown">
                                     <a type="button" class="btn btn-outline--primary text-nowrap btn-block"
                                         href="{{ route('admin.branch.export', request()->only(['searchValue', 'branch_id', 'product_id', 'attribute'])) }}">
                                         <img width="14"
                                             src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}"
                                             class="excel" alt="">
                                         <span class="ps-2">{{ translate('export') }}</span>
                                     </a>
                                 </div>
                                 <!--  <a href="{{ route('admin.branch.add') }}" type="button" class="btn btn--primary text-nowrap">
                                        <i class="tio-add"></i>
                                        {{ translate('add_New_Branch') }}
                                    </a> -->
                             </div>
                         </div>
                     </div>
                     <!-- <div class="table-responsive">
                        <table
                            style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th class="text-center">{{ translate('SL') }}</th>

                                    <th>{{ translate('branch_name') }}</th>
                                    <th>{{ translate('product_name') }}</th>
                                    <th>{{ translate('variation') }}</th>

                                    <th class="text-center">{{ translate('Current_stock') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branches as $key => $stock)
                                <tr>
                                    <td class="text-center">{{ $key + 1 + ($branches->currentPage() - 1) * $branches->perPage() }}</td>
                                    <td>{{ $stock->branch->branch_name ?? translate('N/A') }}</td>
                                    <td>{{ $stock->product->name ?? translate('N/A') }}</td>
                                    <td>
                                        @if ($stock->variation_type)
    <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-soft-primary">{{ $stock->variation_type }}</span>
                                            @if ($stock->variation_key)
    <small class="text-muted">
                                                ({{ Str::replace('|', ' • ', Str::replace(':', ' : ', $stock->variation_key)) }})
    </small>
    @endif
                                        </div>
@else
    <span class="badge badge-soft-dark"></span>
    @endif
                                    </td>
                                    <td class="text-center">{{ $stock->total_stock }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ translate('No data available') }}</td>
                                </tr>
    @endforelse
                            </tbody>
                        </table>
                    </div> -->

                     <div class="table-responsive">
                         <table style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
                             class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                             <thead class="thead-light thead-50 text-capitalize">
                                 <tr>
                                     <th class="text-center">{{ translate('SL') }}</th>
                                     <th>{{ translate('branch_name') }}</th>
                                     <th>{{ translate('product_name') }}</th>
                                     <th>{{ translate('variation') }}</th>
                                     <th class="text-center">{{ translate('Current_stock') }}</th>
                                     <th class="text-center">{{ translate('Actions') }}</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @forelse($branches as $key => $stock)
                                     <tr>
                                         <td class="text-center">
                                             {{ $key + 1 + ($branches->currentPage() - 1) * $branches->perPage() }}</td>
                                         <td>{{ $stock->branch->branch_name ?? translate('N/A') }}</td>
                                         <td>{{ $stock->product->name ?? translate('N/A') }}</td>
                                         <td>
                                             @if ($stock->variation_type)
                                                 <div class="d-flex align-items-center gap-2">
                                                     <span
                                                         class="badge badge-soft-primary">{{ $stock->variation_type }}</span>
                                                     @if ($stock->variation_key)
                                                         <small class="text-muted">
                                                             ({{ Str::replace('|', ' • ', Str::replace(':', ' : ', $stock->variation_key)) }})
                                                         </small>
                                                     @endif
                                                 </div>
                                             @else
                                                 <span class="badge badge-soft-dark">{{ __('N/A') }}</span>
                                             @endif
                                         </td>
                                         <td class="text-center">{{ $stock->total_stock }}</td>
                                         <!-- <td class="text-center">
                                         <button type="button"
                                             class="btn btn-sm btn-outline-primary view-history-btn"
                                             onclick="showStockHistory(this)"
                                             data-branch-id="{{ $stock->branch_id }}"
                                             data-product-id="{{ $stock->product_id }}"
                                             data-branch-name="{{ $stock->branch->branch_name ?? 'N/A' }}"
                                             data-product-name="{{ $stock->product->name ?? 'N/A' }}"
                                             data-variation-type="{{ $stock->variation_type }}"
                                             data-variation-key="{{ $stock->variation_key }}"
                                             data-current-stock="{{ $stock->total_stock }}"
                                             data-history="{{ json_encode($stock->transfer_logs) }}">
                                             <i class="tio-history"></i> {{ translate('View History') }}
                                         </button>
                                     </td> -->


                                         <td class="text-center">
                                             <a href="{{ route('admin.branch.stock-history', [
                                                 'branch_id' => $stock->branch_id,
                                                 'product_id' => $stock->product_id,
                                                 'variation_type' => $stock->variation_type,
                                                 'variation_key' => $stock->variation_key,
                                             ]) }}"
                                                 class="btn btn-sm btn-outline-primary">
                                                 <i class="tio-history"></i> {{ translate('View History') }}
                                             </a>
                                         </td>


                                     </tr>
                                 @empty
                                     <tr>
                                         <td colspan="6" class="text-center">{{ translate('No data available') }}</td>
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
                     @if (count($branches) == 0)
                         @include(
                             'layouts.back-end._empty-state',
                             ['text' => 'no_data_found'],
                             ['image' => 'default']
                         )
                     @endif
                 </div>
             </div>
         </div>
     </div>
     <!-- Stock History Modal -->
     <div class="modal fade" id="stockHistoryModal" tabindex="-1" role="dialog"
         aria-labelledby="stockHistoryModalLabel" aria-hidden="true" style="overflow: visible !important;">
         <div class="modal-dialog modal-lg" role="document"
             style="box-shadow: 0 12px 30px rgba(0,0,0,0.28), 0 35px 70px rgba(0,0,0,0.35);
            border-radius:14px;">
             <div class="modal-content"
                 style="box-shadow:none !important; border-radius:14px; background-clip:padding-box;">
                 <div class="modal-header">
                     <h5 class="modal-title" id="stockHistoryModalLabel">
                         {{ translate('Stock Transfer History') }}
                     </h5>
                     <button type="button" class="close" onclick="closeModal()"
                         aria-label="{{ translate('Close') }}">
                         <span aria-hidden="true">&times;</span>
                     </button>
                 </div>
                 <div class="modal-body">


                     <div class="row mb-4">
                         <div class="col-md-6">
                             <div class="mb-2">
                                 <strong>{{ translate('Branch') }}:</strong>
                                 <span id="modalBranchName">-</span>
                             </div>
                             <div>
                                 <strong>{{ translate('Product') }}:</strong>
                                 <span id="modalProductName">-</span>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="mb-2">
                                 <strong>{{ translate('Variation') }}:</strong>
                                 <span id="modalVariation">-</span>
                             </div>
                             <div>
                                 <strong>{{ translate('Current Stock') }}:</strong>
                                 <span id="modalCurrentStock">-</span>
                             </div>
                         </div>
                     </div>
                     <a type="button" class="btn btn-outline--primary text-nowrap p-2" id="exportStockHistory"
                         data-base-url="{{ route('admin.branch.export') }}" href="#">
                         <img width="14" src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}"
                             class="excel" alt="">
                         <span class="ps-1">{{ translate('export') }}</span>
                     </a>

                     <hr>

                     <div id="historyTableContainer" style="max-height: 400px; overflow-y: auto;">
                         <table class="table table-sm table-bordered">
                             <thead class="thead-light sticky-top">
                                 <tr>
                                     <th>{{ translate('Date') }}</th>
                                     <th>{{ translate('Type') }}</th>
                                     <th>{{ translate('Quantity') }}</th>
                                     <th>{{ translate('Reference') }}</th>
                                     <th>{{ translate('Status') }}</th>
                                 </tr>
                             </thead>
                             <tbody id="historyTableBody">
                                 <!-- History data will be loaded here -->
                             </tbody>
                         </table>
                     </div>

                     <div id="noHistoryMessage" class="text-center text-muted py-4" style="display: none;">
                         <i class="tio-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                         <p class="mt-2">{{ translate('No transfer history found') }}</p>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" onclick="closeModal()">
                         {{ translate('Close') }}
                     </button>
                 </div>
             </div>
         </div>
     </div>
 @endsection
 @push('script')
     <script>
         function showStockHistory(button) {
             // 1. Extract data from button attributes
             const branchId = button.getAttribute('data-branch-id');
             const branchName = button.getAttribute('data-branch-name');
             const productName = button.getAttribute('data-product-name');
             const variationType = button.getAttribute('data-variation-type');
             const variationKey = button.getAttribute('data-variation-key');
             const currentStock = button.getAttribute('data-current-stock');

             // Parse the history collection passed from the controller
             const historyData = JSON.parse(button.getAttribute('data-history') || '[]');

             // 2. Update Export Link
             const exportBtn = document.getElementById('exportStockHistory');
             const baseUrl = exportBtn.getAttribute('data-base-url');
             const productId = button.getAttribute('data-product-id');
             exportBtn.href =
                 `${baseUrl}?product_id=${productId}&branch_id=${branchId}&variation_type=${encodeURIComponent(variationType)}`;

             // 3. Update Modal Header Info
             document.getElementById('modalBranchName').textContent = branchName;
             document.getElementById('modalProductName').textContent = productName;
             document.getElementById('modalCurrentStock').textContent = currentStock;

             // Format variation display text
             let variationText = 'N/A';
             if (variationType && variationType !== 'No Variation') {
                 variationText = variationType;
                 if (variationKey && variationKey !== 'No Variation') {
                     variationText += ` (${variationKey.replace(/\|/g, ' • ').replace(/:/g, ' : ')})`;
                 }
             }
             document.getElementById('modalVariation').textContent = variationText;

             // 4. Process and Render History Table
             const historyTableBody = document.getElementById('historyTableBody');
             const historyTableContainer = document.getElementById('historyTableContainer');
             const noHistoryMessage = document.getElementById('noHistoryMessage');

             historyTableBody.innerHTML = ''; // Clear previous rows

             if (historyData.length > 0) {
                 let html = '';
                 historyData.forEach(function(log) {
                     // Determine styling based on IN/OUT type
                     const isStockIn = log.type === 'IN';
                     const typeClass = isStockIn ? 'text-success' : 'text-danger';
                     const quantitySign = isStockIn ? '+' : '-';
                     const typeText = isStockIn ? '{{ translate('Stock In') }}' : '{{ translate('Stock Out') }}';

                     // Determine Description based on the reason (Manual vs Transfer)
                     let description = '';
                     if (log.reference === 'BRANCH TRANSFER') {
                         // Logic for transfers using branch names
                         description = isStockIn ?
                             `{{ translate('Received from') }} ${log.from_branch ?? 'Branch'}` :
                             `{{ translate('Sent to') }} ${log.to_branch ?? 'Branch'}`;
                     } else {
                         // Logic for Manual Adjustments or Initial Stock
                         description = log.remarks || log.reference;
                     }

                     // Format the Date
                     const dateObj = new Date(log.created_at);
                     const formattedDate = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString([], {
                         hour: '2-digit',
                         minute: '2-digit'
                     });

                     html += `
                    <tr>
                        <td>${formattedDate}</td>
                        <td><span class="${typeClass} font-weight-bold">${typeText}</span></td>
                        <td class="${typeClass} font-weight-bold">
                            ${quantitySign} ${log.quantity}
                        </td>
                        <td>
                            <strong>${log.reference}</strong>
                            <br>
                            <small class="text-muted">${description}</small>
                        </td>
                        <td>
                            <span class="badge badge-success">
                                ${log.status || '{{ translate('completed') }}'}
                            </span>
                        </td>
                    </tr>
                `;
                 });

                 historyTableBody.innerHTML = html;
                 historyTableContainer.style.display = 'block';
                 noHistoryMessage.style.display = 'none';
             } else {
                 historyTableContainer.style.display = 'none';
                 noHistoryMessage.style.display = 'block';
             }

             // 5. Show the Modal
             $('#stockHistoryModal').modal('show');
         }

         // Helper to close modal properly and clean up backdrops
         function closeModal() {
             $('#stockHistoryModal').modal('hide');
             $('body').removeClass('modal-open');
             $('.modal-backdrop').remove();
             document.body.style.overflow = 'auto';
         }
     </script>
 @endpush

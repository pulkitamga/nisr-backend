@php($holdOrders = collect($cartItems ?? [])->filter(fn($item) => (bool)($item['customerOnHold'] ?? false))->all())

@if (count($holdOrders) > 0)
    <div class="table-responsive datatable-custom custom-scrollbar-pos min-h-300">
        <table class="table table-hover table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
            <thead class="thead-light thead-50 text-capitalize">
            <tr>
                <th>{{translate('SL')}}</th>
                <th>{{translate('date')}}</th>
                <th>{{translate('customer_info')}}</th>
                <th>{{translate('quantity')}}</th>
                <th>{{translate('total_amount')}}</th>
                <th class="text-center">{{translate('action')}}</th>
            </tr>
            </thead>

            <tbody>
            @php($totalHoldOrdersCount=1)
                @foreach ($holdOrders as $key => $singleCart)
                    <tr>
                        <td>{{ $totalHoldOrdersCount }}</td>
                            <?php $totalHoldOrdersCount++; ?>
                        <td>
                            @php($holdTime = !empty($singleCart['add_to_cart_time']) ? \Carbon\Carbon::parse($singleCart['add_to_cart_time']) : null)
                            @if ($holdTime)
                                <div>{{ $holdTime->format('d/m/Y') }}</div>
                                <div>{{ $holdTime->format('h:i A') }}</div>
                            @else
                                <div>{{ translate('now') }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $singleCart['customerName'] }}</div>
                            <a href="tel:{{ $singleCart['customerPhone'] ?? '' }}"
                               class="text-dark">{{ $singleCart['customerPhone'] ?? '' }}</a>
                        </td>
                        <td>
                                <div class="table-items">
                                    <div class="cursor-pointer">
                                        {{ $singleCart['countItem'] }} {{ translate('items') }}
                                    </div>
                                @if (!empty($singleCart['cartItemValue']) && count($singleCart['cartItemValue']) > 0)
                                    <div class="bg-white p-0 box-shadow table-items-popup">
                                        @foreach($singleCart['cartItemValue'] as  $item)
                                            @if(is_array($item))
                                                <div class="p-3 border-bottom rounded d-flex justify-content-between gap-2">
                                                    <div class="media gap-2">
                                                        <img width="40" alt="" class="aspect-1 object-fit-cover rounded"
                                                             src="{{ getStorageImages(path: $item['image'], type: 'backend-product') }}">
                                                        <div class="media-body">
                                                            <h6 class="text-truncate"> {{ Str::limit($item['name'], 12 )}}</h6>
                                                            @if($item['variant'])
                                                                <div class="text-muted">{{ translate('variation') }}
                                                                    : {{ $item['variant'] }}</div>
                                                            @endif
                                                            <div class="text-muted">{{ translate('qty') }}
                                                                : {{ $item['quantity'] }}</div>
                                                        </div>

                                                    </div>
                                                    <h5>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $item['productSubtotal']), currencyCode: getCurrencyCode())}}</h5>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: round((float)($singleCart['total'] ?? 0), 2)), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-soft-warning action-cart-change" data-cart="{{ $key }}">
                                    {{ translate('resume') }}
                                </button>
                                <button type="button" class="btn btn-soft-danger action-cancel-customer-order"
                                        data-cart-id="{{ $key }}">
                                    {{ translate('cancel_order') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="d-flex align-items-center justify-content-center h-100">
        <div>
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product.svg') }}" alt="">
            <h4 class="text-muted text-center mt-4">{{ translate('No_Order_Found') }}</h4>
        </div>
    </div>
@endif

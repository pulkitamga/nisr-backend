<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
    <table>
        <thead>
            <tr>
                <th style="font-size:18px">{{translate('customer_Reviews')}}</th>
            </tr>
            <tr>

                <th>{{ translate('search_Criteria').' '.'-' }}</th>
                <th></th>
                <th>
                    {{translate('search_Bar_Content').' '.'-'.' '.!empty($data['key']) ?  ucwords($data['key']) : 'N/A'}}
                    @if(isset($data['vendor']))
                        <br>
                        {{translate('Store_Name')}} - {{$data['vendor']?->shop?->name}}
                    @endif
                    <br>
                        {{translate('Product').' '.'-'.' '.ucwords($data['product_name'] == 'all_products' ? translate('all_Products') : $data['product_name'])}}
                    <br>
                        {{translate('Customer').' '.'-'.' '.ucwords($data['customer_name'] == 'all_customers' ? translate('all_Customers') : $data['customer_name']['f_name'].' '.$data['customer_name']['l_name'])}}
                    <br>
                         {{translate('Status').' '.'-'.' '.translate(!is_null($data['status']) ? ($data['status'] == 1 ? 'active' : 'inactive') : 'all_status')}}
                    <br>
                        {{translate('From').' '.'-'.' '.($data['from'] ?   date('d M, Y',strtotime($data['from'])) : '') }}
                    <br>
                        {{translate('To').' '.'-'.' '. ($data['to'] ? date('d M, Y',strtotime($data['to'])) : '')}}
                    <br>
                </th>
            </tr>
            <tr>
                <td> {{translate('SL')}}	</td>
                <td> {{translate('Product_name')}}	</td>
                <td> {{translate('Customer_Name')}}	</td>
                @if($showStoreColumn)
                <td> {{translate('Store_Name')}}	</td>
                @endif
                <td> {{translate('Item_Price')}}	</td>
                <td> {{translate('Rating')}}</td>
                <td> {{translate('Review')}}</td>
            </tr>
            @foreach ($data['reviews'] as $key=>$item)
                <tr >
                    <td > {{++$key}}	</td>
                    <td> {{$item?->product?->name ?? translate('product_not_found')}}	</td>
                    <td>{{ucwords(($item->customer?->f_name ?? translate('customer_not_found')).' '.$item->customer?->l_name)}}</td>
                    @if($showStoreColumn)
                    <td> {{ucwords($item?->product?->seller?->shop->name ?? translate('Store_not_found'))}}	</td>
                    @endif
                    <td> {{$item?->product ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $item?->product->unit_price ?? 0)) : translate('not_found')}}</td>
                    <td> {{$item?->rating}}</td>
                    <td> {{$item?->comment}}</td>
                </tr>
            @endforeach
        </thead>
    </table>
</html>

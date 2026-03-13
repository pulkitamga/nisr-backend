<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
        <tr>
            <th>{{ translate('Sr No') }}</th>
            <th>{{ translate('Product Name') }}</th>
            <th>{{ translate('Product Category') }}</th>
            <th>{{ translate('Product Subcategory') }}</th>
            <th>{{ translate('Product Attribute No') }}</th>
            <th>{{ translate('Min Qty') }}</th>
            <th>{{ translate('Max Qty') }}</th>
            <th>{{ translate('Price Per Product') }}</th>

        </tr>
    </thead>
    <tbody>
        <?php //echo "<pre>"; print_r($data)  
        ?>
        @php $srNo = 1; @endphp
        @foreach ($data as $index => $row)
        @if ($index >= count($data) - 1) @break @endif
        @foreach ($row['price_ranges'] as $price_range)
        <tr>
            <td>{{ $srNo++ }}</td>
            <td>{{ $row['product_name'] }}</td>
            <td>{{ $row['category_name'] }}</td>
            <td>{{ $row['sub_category_name'] }}</td>
            <td>{{ $row['attribute_id'] }}</td>
            <td>{{ $price_range['min_qty'] }}</td>
            <td>{{ $price_range['max_qty'] }}</td>
            <td>{{setCurrencySymbol(amount: usdToDefaultCurrency($price_range['price_per_piece'] ?? 0), currencyCode: getCurrencyCode())}}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>

</html>
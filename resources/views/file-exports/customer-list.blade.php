<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
    <tr>
        <th style="font-size:18px">{{translate('Customer_List')}}</th>
    </tr>
    <tr>
        <th>{{ translate('customer_Analytics').' '.'-' }}</th>
        <th></th>
        <th>
            {{translate('Total_Customer').' '.'-'.' '.count($data['customers'])}}
        </th>
    </tr>
    <tr>
        <th>{{translate('Filter_Criteria')}}-</th>
        <th></th>
        <th>
            {{ translate('Status') . ' - ' . ($data['status'] === '1' ? 'Active' : ($data['status'] === '0' ? 'Inactive' : 'All')) }}
            <br>
            {{ translate('Sort_By') . ' - ' . (!empty($data['sortBy']) ? ($data['sortBy'] === 'order_amount' ? translate('Sort_By_Order_Amount') : ($data['sortBy']  === 'asc' ? translate('Sort_By_Oldest') : translate('Sort_By_Newest'))) : 'N/A') }}
            <br>
            {{ translate('Choose_First'). ' - ' . (!empty($data['chooseFirst']) ? $data['chooseFirst'] : 'N/A')}}
            <br>
            {{ translate('search_Bar_Content'). ' - ' . (!empty($data['searchValue']) ? $data['searchValue'] : 'N/A')}}
            <br>
            {{translate('order_start_date').' - '. (!empty($data['orderStartDate']) ?  $data['orderStartDate']->format('d F Y') : 'N/A') }}
            <br>
            {{translate('order_end_date').' - '. (!empty($data['orderEndDate']) ?  $data['orderEndDate']->format('d F Y') : 'N/A') }}
            <br>
            {{translate('joining_start_date').' - '. (!empty($data['joiningStartDate']) ?  $data['joiningStartDate']->format('d F Y') : 'N/A') }}
            <br>
            {{translate('joining_end_date').' - '. (!empty($data['joiningEndDate']) ?  $data['joiningEndDate']->format('d F Y') : 'N/A') }}

        </th>
    </tr>
    <tr>
        <td>{{ translate('SL') }}</td>
        <td>{{ translate('customer_Image') }}</td>
        <td>{{ translate('Name') }}</td>
        <td>{{ translate('Phone') }}</td>
        <td>{{ translate('Email') }}</td>
        <td>{{ translate('date_of_Joining') }}</td>
        <td>{{ translate('total_Order') }}</td>
        <td>{{ translate('Status') }}</td>
    </tr>
    @foreach ($data['customers'] as $key=>$item)
        <tr>
            <td> {{++$key}}    </td>
            <td style="height:80px"></td>
            <td>{{ ucwords(($item->f_name?? translate('not_found')).' '.$item->l_name) }}</td>
            <td>{{ (string)$item?->phone ?? translate('not_found') }}</td>
            <td>{{ ucwords($item->email) }}</td>
            <td>{{ date('d M, Y ',strtotime($item->created_at)) }}</td>
            <td>{{ $item->orders->count() ?? 0 }}</td>
            <td>{{ translate($item->is_active == 1 ? 'active' : 'inactive') }}</td>
        </tr>
    @endforeach
    </thead>
</table>
</html>

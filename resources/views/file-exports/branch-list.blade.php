<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
    <table>
        <thead>
            <tr>
                <th style="font-size: 18px">{{translate('Branch_List')}}</th>
            </tr>
            
            <tr>
                <th>{{translate('search_Criteria')}}-</th>
                <th></th>
                <th>  {{translate('search_Bar_Content')}} - {{!empty($data['search']) ? $data['search'] : 'N/A'}}</th>
            </tr>
            <tr>
                <td> {{translate('SL')}}</td>
               
              
                <td> {{translate('Branch_Name')}}</td>
                <td> {{translate('Branch_Address')}}</td>
                <td> {{translate('branch_Zipcode')}}</td>
                <td> {{translate('Phone')}}	</td>
                <td> {{translate('Email')}}	</td>
                <td> {{translate('joined_At')}}	</td>
                
                <td> {{translate('Status')}}</td>
            </tr>
            @foreach ($data['vendors'] as $key=>$item)
                <tr>
                    <td> {{++$key}}	</td>
                    
                    
                    <td> {{ucwords($item->branch_name)}}</td>
                    <td> {{ucwords($item->branch_address)}}</td>
                    <td> {{ucwords($item->branch_zipcode)}}</td>
                    <td> {{$item?->phone ?? translate('not_found')}}</td>
                    <td> {{ucwords($item->email)}}</td>
                    <td> {{date('d M, Y h:i A',strtotime($item->created_at))}}</td>
                     
                    <td> {{translate($item->status == 'active' ? 'active' : 'inactive')}}</td>
                </tr>
            @endforeach
        </thead>
    </table>
</html>

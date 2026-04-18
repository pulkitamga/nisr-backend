<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
        <tr>
            <th style="font-size: 18px">{{translate('wholesaler_List')}}</th>
        </tr>
        <tr>

            <th>{{ translate('request_Analytics') .' '.'-'}}</th>
            <th></th>
            <th>
                {{translate('filter_By').' '.'-'.' '.ucwords($data['filter'])}}
                <br>
                {{translate('total__Request').' '.'-'.' '.count($data['wholesaler'])}}

            </th>
        </tr>
       
        <tr>
            <td>{{translate('SL')}}</td>
            <td>{{translate('Wholesaler')}}</td>
            <td>{{translate('Company')}}</td>
            <td>{{translate('Email')}}</td>
            <td>{{translate('Mobile')}}</td>
            <td>{{translate('wholesaler_discount')}}</td>
            <td>{{translate('Tier')}}</td>
            <td>{{translate('moq_status')}}</td>
            <td>{{translate('wholesaler_status')}}</td>
            <td>{{translate('trade')}}</td>
            <td>{{translate('reg._no.')}}</td>
            <td>{{translate('tax._no.')}}</td>
            <td>{{translate('VAT._no.')}}</td>
        </tr>
        @foreach ($data['wholesaler'] as $key=>$business)
        <tr>
            <td> {{++$key}} </td>
            <td>{{ $business->wholesaler->name ?? 'N/A' }}</td>
            <td>{{ $business->company_name ?? 'N/A' }}</td>
                        <td>{{ $business->wholesaler->email ?? 'N/A' }}</td>
            <td>{{ $business->wholesaler->phone ?? 'N/A' }}</td>
            <td>{{ $business->wholesaler->wholesaler_discount ?? 'N/A' }}</td>
            <td>{{ $business->wholesaler->tier ?? 'N/A' }}</td>
            <td>{{ $business->wholesaler->moq_override_enabled  == 1 ? 'active' : 'deactive' }}</td>
            <td>{{ $business->wholesaler->is_active  == 1 ? 'active' : 'inactive' }}</td>
            <td>{{ $business->trade_name ?? 'N/A' }}</td>
            <td>
                {{ $business->registration_number ?? 'N/A' }}
                @if($business->register_copy)
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#registrationModal{{$business->id}}">{{ __('View') }}</button>
                @endif
            </td>
            <td>
                {{ $business->tax_id ?? 'N/A' }}
                @if($business->tax_card_copy)
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#taxModal{{$business->id}}">{{ __('View') }}</button>
                @endif
            </td>
            <td>
                {{ $business->vat_number ?? 'N/A' }}
                @if($business->vat_register_copy)
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#vatModal{{$business->id}}">{{ __('View') }}</button>
                @endif
            </td>
        </tr>
        @endforeach
    </thead>
</table>

</html>

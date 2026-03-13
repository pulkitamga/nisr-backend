<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
        <tr>
            <th style="font-size: 18px">{{translate('wholesaler_request_List')}}</th>
        </tr>
        <tr>

            <th>{{ translate('request_Analytics') .' '.'-'}}</th>
            <th></th>
            <th>
                {{translate('filter_By').' '.'-'.' '.ucwords($data['filter'])}}
                <br>
                {{translate('total_request').' '.'-'.' '.count($data['wholesaler'])}}

            </th>
        </tr>
       
        <tr>
            <td>{{translate('SL')}}</td>
            <td>{{translate('wholesaler')}}</td>
            <td>{{translate('company')}}</td>
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

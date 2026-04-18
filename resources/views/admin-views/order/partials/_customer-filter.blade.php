<label class="form-label" for="customer_id_value">{{ translate('Customer') }}</label>
<input type="hidden" id="customer_id" name="customer_id" value="{{ request('customer_id') ? request('customer_id') : 'all' }}">
<select
    id="customer_id_value"
    data-placeholder-default="{{ translate('all_customer') }}"
    data-placeholder="@if($customer == 'all')
        {{ translate('all_customer') }}
    @else
        {{ $customer->name ?? trim(($customer->f_name ?? '') . ' ' . ($customer->l_name ?? '')) . (!empty($customer->phone) ? ' (' . $customer->phone . ')' : '') }}
    @endif"
    class="js-data-example-ajax form-control form-ellipsis"
>
    <option value="all">{{ translate('all_customer') }}</option>
</select>

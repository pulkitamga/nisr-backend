@php
use App\Enums\ViewPaths\Admin\BusinessSettings;
@endphp
<div class="inline-page-menu my-4">
        <ul class="list-unstyled">
                <li class="{{ request()->routeIs('admin.business-settings.web-config.*') ?'active':'' }}"><a
                                href="{{route('admin.business-settings.web-config.index')}}">{{translate('General')}}</a></li>
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.payment-method.*') ?'active':'' }}">
                        <a
                                href="{{route('admin.business-settings.payment-method.payment-option')}}">{{translate('payment_options')}}</a>
                </li>
                <li class="{{ request()->routeIs('admin.product-settings.*') ?'active':'' }}"><a
                                href="{{ route('admin.product-settings.index') }}">{{translate('Products')}}</a>
                </li>
                <li class="{{ request()->routeIs('admin.warranty-settings.*') ?'active':'' }}"><a
                                href="{{ route('admin.warranty-settings.index') }}">{{translate('Warranty')}}</a>
                </li>
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.priority-setup.*') ?'active':'' }}">
                        <a href="{{route('admin.business-settings.priority-setup.index')}}">{{translate('priority_setup')}}</a>
                </li>
                <li class="{{ request()->routeIs('admin.business-settings.order-settings.*') ?'active':'' }}">
                        <a href="{{route('admin.business-settings.order-settings.index')}}">{{translate('Orders')}}</a>
                </li>
                <!-- <li class="{{ Request::is('admin/business-settings/vendor-settings') ?'active':'' }}"><a
                href="{{route('admin.business-settings.vendor-settings.index')}}">{{translate('vendors')}}</a></li> -->
                <li class="{{ request()->routeIs('admin.customer.customer-settings') ?'active':'' }}"><a
                                href="{{route('admin.customer.customer-settings')}}">{{translate('customers')}}</a></li>
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.delivery-man-settings.*') ?'active':'' }}"><a
                                href="{{route('admin.business-settings.delivery-man-settings.index')}}">{{translate('delivery_men')}}</a>
                </li>
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.shipping-method.*') ?'active':'' }}"><a
                                href="{{route('admin.business-settings.shipping-method.index')}}">{{translate('shipping_Method')}}</a>
                </li>
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.delivery-restriction.*') ? 'active':'' }}"><a
                                href="{{ route('admin.business-settings.delivery-restriction.index') }}">{{translate('Delivery_restriction')}}</a>
                </li>
                <!-- <li class="text-capitalize {{ Request::is('admin/business-settings/delivery-restriction') ? 'active':'' }}"><a
                                href="{{ route('admin.business-settings.delivery-restriction.index') }}">{{translate('delivery_available')}}</a>
                </li> -->
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.state-city.*') ? 'active':'' }}"><a
                                href="{{ route('admin.business-settings.state-city.index') }}">{{translate('State_&_City')}}</a>
                </li>
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.invoice-settings.*') ? 'active':'' }}"><a
                                href="{{ route('admin.business-settings.invoice-settings.index') }}">{{translate('Invoice')}}</a>
                </li>
                <li class="text-capitalize {{ request()->routeIs('admin.business-settings.quotation-settings.*') ? 'active':'' }}"><a
                                href="{{ route('admin.business-settings.quotation-settings.index') }}">{{translate('Quotation')}}</a>
                </li>
        </ul>
</div>

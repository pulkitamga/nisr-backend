@php use App\Enums\ViewPaths\Admin\FeaturesSection;use App\Enums\ViewPaths\Admin\Pages; use App\Enums\ViewPaths\Admin\BusinessSettings; @endphp
<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/business-settings/'.Pages::TERMS_CONDITION[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.terms-condition')}}">{{translate('terms_&_Conditions')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::SERVICE_POLICY[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.service-policy')}}">{{translate('Service_policy')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::PRIVACY_POLICY[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.privacy-policy')}}">{{translate('Privacy_Policy')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::PRIVACY_POLICY[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.warranty-policy')}}">{{translate('warranty_Policy')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::VIEW[URI].'/refund-policy') ?'active':'' }}">
            <a href="{{route('admin.business-settings.page',['refund-policy'])}}">{{translate('Refund_Policy')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::VIEW[URI].'/return-policy') ?'active':'' }}">
            <a href="{{route('admin.business-settings.page',['return-policy'])}}">{{translate('Return_Policy')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::VIEW[URI].'/cancellation-policy') ?'active':'' }}">
            <a href="{{route('admin.business-settings.page',['cancellation-policy'])}}">{{translate('Cancellation_Policy')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::VIEW[URI].'/shipping-policy') ?'active':'' }}">
            <a href="{{ route('admin.business-settings.page', ['shipping-policy']) }}">{{translate('Shipping_Policy')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::ABOUT_US[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.about-us')}}">{{translate('About_us')}}</a>
        </li>
         <li class="{{ Request::is('admin/business-settings/'.Pages::COOKIE_SETTINGS[URI]) ? 'active':'' }}">
            <a href="{{ route('admin.business-settings.cookie-settings') }}">{{translate('cookies')}}</a>
        </li>
        <li class="{{ Request::is('admin/helpTopic/'.\App\Enums\ViewPaths\Admin\HelpTopic::LIST[URI]) ?'active':'' }}">
            <a href="{{route('admin.helpTopic.list')}}">{{translate('FAQ')}}</a>
        </li>
        

        @if(theme_root_path() == 'theme_fashion')
            <li class="{{ Request::is('admin/business-settings/'.FeaturesSection::VIEW[URI]) ?'active':'' }}">
                <a href="{{route('admin.business-settings.features-section')}}">{{translate('features_Section')}}</a>
            </li>
        @endif

        @if(theme_root_path() == 'default')
            <li class="{{ Request::is('admin/business-settings/'.FeaturesSection::COMPANY_RELIABILITY[URI]) ?'active':'' }}">
                <a href="{{route('admin.business-settings.company-reliability')}}" class="text-capitalize">
                    {{translate('company_Reliability')}}
                </a>
            </li>
        @endif

    </ul>
</div>

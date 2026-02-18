<?php

namespace App\Enums\ViewPaths\Admin;

enum   WholesalerRegistrationSetting
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.business-settings.wholesaler-registration-setting.header'
    ];
    const WITH_US = [
        URI => 'with-us',
        VIEW => 'admin-views.business-settings.wholesaler-registration-setting.with-us'
    ];
    const BUSINESS_PROCESS = [
        URI => 'business-process',
        VIEW => 'admin-views.business-settings.wholesaler-registration-setting.business-process'
    ];
    const DOWNLOAD_APP = [
        URI => 'download-app',
        VIEW => 'admin-views.business-settings.wholesaler-registration-setting.download-app'
    ];
    const FAQ = [
        URI => 'faq',
        VIEW => 'admin-views.business-settings.wholesaler-registration-setting.faq'
    ];
    const TOGGLE_TYPE_STATUS = [
        URI => 'toggle',
        VIEW => ''
    ];

}

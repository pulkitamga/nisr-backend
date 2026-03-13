<?php

namespace App\Enums\ViewPaths\Admin;

enum Pages
{
    const VIEW = [
        URI => 'page',
        VIEW => 'admin-views.business-settings.page.page'
    ];

    const TERMS_CONDITION = [
        URI => 'terms-condition',
        VIEW => 'admin-views.business-settings.page.terms-condition'
    ];
    const SERVICE_POLICY = [
        URI => 'Service-policy',
        VIEW => 'admin-views.business-settings.page.service-policy'
    ];
    const WARRANTY_POLICY = [
        URI => 'Warranty-policy',
        VIEW => 'admin-views.business-settings.page.warranty-policy'
    ];

    const PRIVACY_POLICY = [
        URI => 'privacy-policy',
        VIEW => 'admin-views.business-settings.page.privacy-policy'
    ];

    const ABOUT_US = [
        URI => 'about-us',
        VIEW => 'admin-views.business-settings.page.about-us'
    ];
    const COOKIE_SETTINGS = [
        URI => 'cookie-settings',
        VIEW => 'admin-views.business-settings.page.cookie-settings'
    ];
}

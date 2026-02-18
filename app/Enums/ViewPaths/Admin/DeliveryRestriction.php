<?php

namespace App\Enums\ViewPaths\Admin;

enum DeliveryRestriction
{
    const VIEW = [
        URI => '',
        VIEW => 'admin-views.business-settings.delivery-restriction'
    ];

    const ADD = [
        URI => 'add-delivery-country',
        VIEW => ''
    ];

    const DELETE = [
        URI => 'delivery-country-delete',
        VIEW => ''
    ];

    const ZIPCODE_ADD = [
        URI => 'add-zip-code',
        VIEW => ''
    ];

    const ZIPCODE_DELETE = [
        URI => 'zip-code-delete',
        VIEW => ''
    ];

    const AREA_ADD = [
        URI => 'add-area',
        VIEW => ''
    ];

    const AREA_DELETE = [
        URI => 'area-delete',
        VIEW => ''
    ];

    const STATE_ADD = [
        URI => 'add-state',
        VIEW => ''
    ];

    const STATE_DELETE = [
        URI => 'state-delete',
        VIEW => ''
    ];

    const CITY_ADD = [
        URI => 'add-city',
        VIEW => ''
    ];

    const CITY_DELETE = [
        URI => 'city-delete',
        VIEW => ''
    ];

    const COUNTRY_RESTRICTION = [
        URI => 'country-restriction-status-change',
        VIEW => ''
    ];

    const STATE_RESTRICTION = [
        URI => 'state-restriction-status-change',
        VIEW => ''
    ];

    const CITY_RESTRICTION = [
        URI => 'city-restriction-status-change',
        VIEW => ''
    ];

    const ZIPCODE_RESTRICTION = [
        URI => 'zipcode-restriction-status-change',
        VIEW => ''
    ];

    const AREA_RESTRICTION = [
        URI => 'area-restriction-status-change',
        VIEW => ''
    ];
}

(function () {
    const config = window.serviceRequestConfig || null;

    if (!config) {
        return;
    }

    const serviceRequestModalElement = document.getElementById('serviceRequestModal');
    const serviceRequestForm = document.getElementById('serviceRequestForm');
    const confirmServiceRequestButton = document.getElementById('confirmServiceRequest');
    const mobileOption = document.getElementById('mobile');
    const inShopOption = document.getElementById('inShop');
    const mobileAddress = document.getElementById('mobileAddress');
    const mobileGuidance = document.getElementById('mobileGuidance');
    const inShopGuidance = document.getElementById('inShopGuidance');
    const makeSelect = document.getElementById('makeSelect');
    const modelSelect = document.getElementById('modelSelect');
    const modelOptions = document.getElementById('vehicle-model-options');

    function withJquery(callback) {
        if (typeof window.jQuery === 'undefined') {
            return;
        }

        callback(window.jQuery);
    }

    function normalizeValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function stringifyOptionLabel(value) {
        if (typeof value === 'string' || typeof value === 'number') {
            return String(value);
        }

        if (value && typeof value === 'object') {
            const directValue = [value.name, value.value, value.label].find((item) => typeof item === 'string' && item.trim() !== '');
            if (directValue) {
                return directValue;
            }

            const nestedValue = Object.values(value).find((item) => typeof item === 'string' && item.trim() !== '');
            if (nestedValue) {
                return nestedValue;
            }
        }

        return '';
    }

    function populateModels(makeName, selectedModel) {
        if (!modelOptions) {
            return;
        }

        modelOptions.innerHTML = '';

        const matchingMake = (config.allMakes || []).find((make) => normalizeValue(make.name) === normalizeValue(makeName));
        if (!matchingMake) {
            return;
        }

        matchingMake.models.forEach((modelName) => {
            const option = document.createElement('option');
            option.value = modelName;
            modelOptions.appendChild(option);
        });

        if (modelSelect && selectedModel) {
            modelSelect.value = selectedModel;
        }
    }

    function toggleAddress() {
        if (!mobileOption || !inShopOption || !mobileAddress || !mobileGuidance || !inShopGuidance) {
            return;
        }

        const isMobile = !!mobileOption.checked;
        mobileAddress.style.display = isMobile ? 'block' : 'none';
        mobileGuidance.style.display = isMobile ? 'flex' : 'none';
        inShopGuidance.style.display = isMobile ? 'none' : 'flex';

        ['address-country', 'address_details'].forEach((id) => {
            const field = document.getElementById(id);
            if (field) {
                field.required = isMobile;
            }
        });
    }

    function initConfirmation() {
        if (!confirmServiceRequestButton || !serviceRequestForm || typeof Swal === 'undefined') {
            return;
        }

        confirmServiceRequestButton.addEventListener('click', function () {
            if (!config.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: config.labels.loginFirst,
                    text: config.labels.loginRequired,
                    confirmButtonText: config.labels.goToLogin,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = config.routes.login;
                    }
                });
                return;
            }

            Swal.fire({
                title: config.labels.confirmTitle,
                text: config.labels.confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: config.labels.confirmButton,
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                setTimeout(() => {
                    if (typeof serviceRequestForm.requestSubmit === 'function') {
                        serviceRequestForm.requestSubmit();
                        return;
                    }

                    if (typeof serviceRequestForm.reportValidity === 'function' && serviceRequestForm.reportValidity()) {
                        serviceRequestForm.submit();
                    }
                }, 300);
            });
        });
    }

    function initModal() {
        if (!serviceRequestModalElement) {
            return;
        }

        serviceRequestModalElement.addEventListener('shown.bs.modal', function () {
            if (typeof window.fetchAndRenderLocation === 'function') {
                window.fetchAndRenderLocation();
            }
        });

        if (config.hasErrors && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(serviceRequestModalElement).show();
        }
    }

    function initVehicleFields() {
        if (makeSelect) {
            makeSelect.addEventListener('input', function () {
                populateModels(this.value, '');
            });
        }

        if (config.oldVehicleMake) {
            populateModels(config.oldVehicleMake, config.oldVehicleModel || '');
        }
    }

    function initLocationSelectors() {
        withJquery(($) => {
            const locationFields = {
                country: $('#address-country'),
                state: $('#address-state'),
                city: $('#address-city'),
                area: $('#address-area'),
            };
            const locationOptionLists = {
                state: $('#service-state-options'),
                city: $('#service-city-options'),
                area: $('#service-area-options'),
            };

            if (!locationFields.country.length) {
                return;
            }

            const hiddenCountryField = $('#country_name');
            const optionCache = {
                states: [],
                cities: [],
                areas: [],
            };

            function normalizeLocationName(value) {
                return String(value || '')
                    .trim()
                    .toLowerCase()
                    .replace(/[^\w\s]/g, ' ')
                    .replace(/\s+/g, ' ');
            }

            function getResponseItems(response, key) {
                if (Array.isArray(response)) {
                    return response;
                }

                if (response && Array.isArray(response[key])) {
                    return response[key];
                }

                return [];
            }

            function populateDatalist($datalist, items, labelResolver) {
                $datalist.empty();

                items.forEach((item) => {
                    const optionLabel = stringifyOptionLabel(labelResolver(item));
                    if (!optionLabel) {
                        return;
                    }

                    $datalist.append(`<option value="${optionLabel}"></option>`);
                });
            }

            function findMatchingItem(items, value) {
                const normalizedValue = normalizeLocationName(value);
                if (!normalizedValue) {
                    return null;
                }

                return items.find((item) => normalizeLocationName(item?.name) === normalizedValue) || null;
            }

            function findMatchingItemByIdOrName(items, value) {
                const stringValue = String(value || '').trim();
                if (!stringValue) {
                    return null;
                }

                if (/^\d+$/.test(stringValue)) {
                    const numericValue = Number(stringValue);
                    const matchedById = items.find((item) => Number(item?.id) === numericValue) || null;
                    if (matchedById) {
                        return matchedById;
                    }
                }

                return findMatchingItem(items, stringValue);
            }

            function clearSuggestions(...keys) {
                keys.forEach((key) => {
                    if (key === 'state') {
                        optionCache.states = [];
                        populateDatalist(locationOptionLists.state, [], (item) => item.name);
                    }

                    if (key === 'city') {
                        optionCache.cities = [];
                        populateDatalist(locationOptionLists.city, [], (item) => item.name);
                    }

                    if (key === 'area') {
                        optionCache.areas = [];
                        populateDatalist(locationOptionLists.area, [], (item) => item.name);
                    }
                });
            }

            function findAddressComponent(components, types) {
                return components.find((component) => types.some((type) => component.types.includes(type))) || null;
            }

            function loadStates(countryCode) {
                if (!countryCode) {
                    clearSuggestions('state', 'city', 'area');
                    return $.Deferred().resolve([]).promise();
                }

                return $.get(config.routes.states, { country: countryCode }).then((response) => {
                    optionCache.states = getResponseItems(response, 'states');
                    populateDatalist(locationOptionLists.state, optionCache.states, (item) => item.name);
                    clearSuggestions('city', 'area');
                    return optionCache.states;
                });
            }

            function loadCitiesForStateName(stateName) {
                const state = findMatchingItem(optionCache.states, stateName);
                if (!state?.id) {
                    clearSuggestions('city', 'area');
                    return $.Deferred().resolve([]).promise();
                }

                return $.get(config.routes.cities, { state_id: state.id }).then((response) => {
                    optionCache.cities = getResponseItems(response, 'cities');
                    populateDatalist(locationOptionLists.city, optionCache.cities, (item) => item.name);
                    clearSuggestions('area');
                    return optionCache.cities;
                });
            }

            function loadAreasForCityName(cityName) {
                const city = findMatchingItem(optionCache.cities, cityName);
                if (!city?.id) {
                    clearSuggestions('area');
                    return $.Deferred().resolve([]).promise();
                }

                return $.get(config.routes.areas, { city_id: city.id }).then((response) => {
                    optionCache.areas = getResponseItems(response, 'areas');
                    populateDatalist(locationOptionLists.area, optionCache.areas, (item) => item.name);
                    return optionCache.areas;
                });
            }

            function restoreLocationSelections() {
                const oldLocation = config.oldLocation || {};
                hiddenCountryField.val(locationFields.country.find('option:selected').data('name') || '');
                locationFields.state.val('');
                locationFields.city.val('');
                locationFields.area.val('');

                return loadStates(locationFields.country.val()).then(() => {
                    const matchedState = findMatchingItemByIdOrName(optionCache.states, oldLocation.state);
                    locationFields.state.val(matchedState?.name || oldLocation.state || '');

                    return loadCitiesForStateName(locationFields.state.val()).then(() => {
                        const matchedCity = findMatchingItemByIdOrName(optionCache.cities, oldLocation.city);
                        locationFields.city.val(matchedCity?.name || oldLocation.city || '');

                        return loadAreasForCityName(locationFields.city.val()).then(() => {
                            const matchedArea = findMatchingItemByIdOrName(optionCache.areas, oldLocation.area);
                            locationFields.area.val(matchedArea?.name || oldLocation.area || '');
                        });
                    });
                });
            }

            window.syncServiceLocationFromCoordinates = function (result) {
                const components = result?.address_components || [];
                const countryComponent = findAddressComponent(components, ['country']);
                const stateComponent = findAddressComponent(components, ['administrative_area_level_1']);
                const cityComponent = findAddressComponent(components, ['locality', 'administrative_area_level_2']);
                const areaComponent = findAddressComponent(components, ['sublocality', 'sublocality_level_1', 'neighborhood', 'administrative_area_level_3']);

                if (countryComponent) {
                    const matchingCountryOption = locationFields.country.find('option').filter(function () {
                        const optionName = normalizeLocationName($(this).data('name') || $(this).text());
                        return optionName === normalizeLocationName(countryComponent.short_name)
                            || optionName === normalizeLocationName(countryComponent.long_name);
                    }).first();

                    if (matchingCountryOption.length) {
                        locationFields.country.val(matchingCountryOption.val());
                    }
                }

                hiddenCountryField.val(locationFields.country.find('option:selected').data('name') || '');

                loadStates(locationFields.country.val()).then(() => {
                    const matchedState = findMatchingItem(optionCache.states, stateComponent?.long_name)
                        || findMatchingItem(optionCache.states, stateComponent?.short_name);

                    if (!matchedState) {
                        return $.Deferred().resolve().promise();
                    }

                    locationFields.state.val(matchedState.name);

                    return loadCitiesForStateName(matchedState.name).then(() => {
                        const matchedCity = findMatchingItem(optionCache.cities, cityComponent?.long_name)
                            || findMatchingItem(optionCache.cities, cityComponent?.short_name);

                        if (!matchedCity) {
                            return $.Deferred().resolve().promise();
                        }

                        locationFields.city.val(matchedCity.name);

                        return loadAreasForCityName(matchedCity.name).then(() => {
                            const matchedArea = findMatchingItem(optionCache.areas, areaComponent?.long_name)
                                || findMatchingItem(optionCache.areas, areaComponent?.short_name);

                            if (matchedArea) {
                                locationFields.area.val(matchedArea.name);
                            }
                        });
                    });
                });
            };

            locationFields.country.on('change', function () {
                hiddenCountryField.val(locationFields.country.find('option:selected').data('name') || '');
                loadStates($(this).val());
            });

            locationFields.state.on('input change', function () {
                loadCitiesForStateName($(this).val());
            });

            locationFields.city.on('input change', function () {
                loadAreasForCityName($(this).val());
            });

            restoreLocationSelections();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initModal();
        initConfirmation();
        initVehicleFields();
        initLocationSelectors();

        if (mobileOption) {
            mobileOption.addEventListener('change', toggleAddress);
        }

        if (inShopOption) {
            inShopOption.addEventListener('change', toggleAddress);
        }

        toggleAddress();
    });
}());

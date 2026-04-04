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

        ['address-country', 'address-state', 'address-city', 'address-area', 'address_details'].forEach((id) => {
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
            const locationSelects = {
                country: $('#address-country'),
                state: $('#address-state'),
                city: $('#address-city'),
                area: $('#address-area'),
            };

            if (!locationSelects.country.length) {
                return;
            }

            const hiddenLocationFields = {
                country: $('#country_name'),
                state: $('#state_name'),
                city: $('#city_name'),
                area: $('#area_name'),
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

            function populateSelect($select, items, placeholder, valueResolver, labelResolver) {
                $select.empty().append(`<option value="">${placeholder}</option>`);

                items.forEach((item) => {
                    const optionValue = valueResolver(item);
                    const optionLabel = labelResolver(item);
                    $select.append(`<option value="${optionValue}" data-name="${optionLabel}">${optionLabel}</option>`);
                });
            }

            function setHiddenField($select, $hiddenField) {
                $hiddenField.val($select.find('option:selected').data('name') || '');
            }

            function findMatchingOptionValue($select, candidates) {
                const normalizedCandidates = candidates
                    .filter(Boolean)
                    .map((candidate) => normalizeLocationName(candidate));

                if (!normalizedCandidates.length) {
                    return '';
                }

                let exactMatch = '';
                let partialMatch = '';

                $select.find('option').each(function () {
                    const optionValue = $(this).val();
                    const optionName = normalizeLocationName($(this).data('name') || $(this).text());

                    if (!optionValue || !optionName) {
                        return;
                    }

                    if (normalizedCandidates.includes(optionName)) {
                        exactMatch = optionValue;
                        return false;
                    }

                    if (!partialMatch && normalizedCandidates.some((candidate) => optionName.includes(candidate) || candidate.includes(optionName))) {
                        partialMatch = optionValue;
                    }
                });

                return exactMatch || partialMatch;
            }

            function findAddressComponent(components, types) {
                return components.find((component) => types.some((type) => component.types.includes(type))) || null;
            }

            function loadStates(countryCode) {
                if (!countryCode) {
                    populateSelect(locationSelects.state, [], config.labels.selectState, (item) => item.id, (item) => item.name);
                    populateSelect(locationSelects.city, [], config.labels.selectCity, (item) => item.id, (item) => item.name);
                    populateSelect(locationSelects.area, [], config.labels.selectArea, (item) => item, (item) => item);
                    hiddenLocationFields.state.val('');
                    hiddenLocationFields.city.val('');
                    hiddenLocationFields.area.val('');
                    return $.Deferred().resolve([]).promise();
                }

                return $.get(config.routes.states, { country: countryCode }).then((response) => {
                    const states = getResponseItems(response, 'states');
                    populateSelect(locationSelects.state, states, config.labels.selectState, (item) => item.id, (item) => item.name);
                    populateSelect(locationSelects.city, [], config.labels.selectCity, (item) => item.id, (item) => item.name);
                    populateSelect(locationSelects.area, [], config.labels.selectArea, (item) => item, (item) => item);
                    hiddenLocationFields.state.val('');
                    hiddenLocationFields.city.val('');
                    hiddenLocationFields.area.val('');
                    return states;
                });
            }

            function loadCities(stateId) {
                if (!stateId) {
                    populateSelect(locationSelects.city, [], config.labels.selectCity, (item) => item.id, (item) => item.name);
                    populateSelect(locationSelects.area, [], config.labels.selectArea, (item) => item, (item) => item);
                    hiddenLocationFields.city.val('');
                    hiddenLocationFields.area.val('');
                    return $.Deferred().resolve([]).promise();
                }

                return $.get(config.routes.cities, { state_id: stateId }).then((response) => {
                    const cities = getResponseItems(response, 'cities');
                    populateSelect(locationSelects.city, cities, config.labels.selectCity, (item) => item.id, (item) => item.name);
                    populateSelect(locationSelects.area, [], config.labels.selectArea, (item) => item, (item) => item);
                    hiddenLocationFields.city.val('');
                    hiddenLocationFields.area.val('');
                    return cities;
                });
            }

            function loadAreas(cityId) {
                if (!cityId) {
                    populateSelect(locationSelects.area, [], config.labels.selectArea, (item) => item, (item) => item);
                    hiddenLocationFields.area.val('');
                    return $.Deferred().resolve([]).promise();
                }

                return $.get(config.routes.areas, { city_id: cityId }).then((response) => {
                    const areas = getResponseItems(response, 'areas');
                    populateSelect(locationSelects.area, areas, config.labels.selectArea, (item) => item, (item) => item);
                    hiddenLocationFields.area.val('');
                    return areas;
                });
            }

            function restoreLocationSelections() {
                const oldLocation = config.oldLocation || {};
                const matchingCountryValue = findMatchingOptionValue(locationSelects.country, [oldLocation.country]);

                if (!matchingCountryValue) {
                    setHiddenField(locationSelects.country, hiddenLocationFields.country);
                    return loadStates(locationSelects.country.val());
                }

                locationSelects.country.val(matchingCountryValue);
                setHiddenField(locationSelects.country, hiddenLocationFields.country);

                return loadStates(matchingCountryValue).then(() => {
                    const matchingStateValue = findMatchingOptionValue(locationSelects.state, [oldLocation.state]);

                    if (!matchingStateValue) {
                        return $.Deferred().resolve().promise();
                    }

                    locationSelects.state.val(matchingStateValue);
                    setHiddenField(locationSelects.state, hiddenLocationFields.state);

                    return loadCities(matchingStateValue).then(() => {
                        const matchingCityValue = findMatchingOptionValue(locationSelects.city, [oldLocation.city]);

                        if (!matchingCityValue) {
                            return $.Deferred().resolve().promise();
                        }

                        locationSelects.city.val(matchingCityValue);
                        setHiddenField(locationSelects.city, hiddenLocationFields.city);

                        return loadAreas(matchingCityValue).then(() => {
                            const matchingAreaValue = findMatchingOptionValue(locationSelects.area, [oldLocation.area]);
                            if (matchingAreaValue) {
                                locationSelects.area.val(matchingAreaValue);
                                setHiddenField(locationSelects.area, hiddenLocationFields.area);
                            }
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
                    const matchingCountryValue = findMatchingOptionValue(locationSelects.country, [
                        countryComponent.short_name,
                        countryComponent.long_name,
                    ]);

                    if (matchingCountryValue) {
                        locationSelects.country.val(matchingCountryValue);
                    }
                }

                setHiddenField(locationSelects.country, hiddenLocationFields.country);

                loadStates(locationSelects.country.val()).then(() => {
                    const matchingStateValue = findMatchingOptionValue(locationSelects.state, [
                        stateComponent?.long_name,
                        stateComponent?.short_name,
                    ]);

                    if (!matchingStateValue) {
                        return $.Deferred().resolve().promise();
                    }

                    locationSelects.state.val(matchingStateValue);
                    setHiddenField(locationSelects.state, hiddenLocationFields.state);

                    return loadCities(matchingStateValue).then(() => {
                        const matchingCityValue = findMatchingOptionValue(locationSelects.city, [
                            cityComponent?.long_name,
                            cityComponent?.short_name,
                        ]);

                        if (!matchingCityValue) {
                            return $.Deferred().resolve().promise();
                        }

                        locationSelects.city.val(matchingCityValue);
                        setHiddenField(locationSelects.city, hiddenLocationFields.city);

                        return loadAreas(matchingCityValue).then(() => {
                            const matchingAreaValue = findMatchingOptionValue(locationSelects.area, [
                                areaComponent?.long_name,
                                areaComponent?.short_name,
                            ]);

                            if (matchingAreaValue) {
                                locationSelects.area.val(matchingAreaValue);
                                setHiddenField(locationSelects.area, hiddenLocationFields.area);
                            }
                        });
                    });
                });
            };

            locationSelects.country.on('change', function () {
                setHiddenField(locationSelects.country, hiddenLocationFields.country);
                loadStates($(this).val());
            });

            locationSelects.state.on('change', function () {
                setHiddenField(locationSelects.state, hiddenLocationFields.state);
                loadCities($(this).val());
            });

            locationSelects.city.on('change', function () {
                setHiddenField(locationSelects.city, hiddenLocationFields.city);
                loadAreas($(this).val());
            });

            locationSelects.area.on('change', function () {
                setHiddenField(locationSelects.area, hiddenLocationFields.area);
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

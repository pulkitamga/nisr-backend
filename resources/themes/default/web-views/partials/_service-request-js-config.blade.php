@php($serviceRequestConfigFields = ['service_option', 'country', 'state', 'city', 'area', 'address', 'latitude', 'longitude', 'vehicle_type', 'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_mileage', 'vin', 'problem_description', 'notes', 'agree_terms'])
@php($serviceRequestHasErrors = collect($serviceRequestConfigFields)->contains(fn($field) => $errors->has($field)))

<script>
    window.serviceRequestConfig = @json([
        'isLoggedIn' => auth()->guard('customer')->check(),
        'hasErrors' => $serviceRequestHasErrors,
        'oldVehicleMake' => old('vehicle_make'),
        'oldVehicleModel' => old('vehicle_model'),
        'oldLocation' => [
            'country' => old('country'),
            'state' => old('state'),
            'city' => old('city'),
            'area' => old('area'),
        ],
        'allMakes' => $makes->map(fn($make) => [
            'name' => $make->name,
            'models' => $make->models->pluck('name')->values(),
        ])->values(),
        'routes' => [
            'login' => route('customer.auth.login'),
            'states' => route('get.states'),
            'cities' => route('get.cities'),
            'areas' => route('get.areas'),
        ],
        'labels' => [
            'loginFirst' => translate('Login First'),
            'loginRequired' => translate('You need to login to request a service.'),
            'goToLogin' => translate('Go to Login'),
            'confirmTitle' => translate('Are you sure?'),
            'confirmText' => translate('You want to confirm this service request?'),
            'confirmButton' => translate('Yes, Confirm'),
            'selectState' => translate('Select State'),
            'selectCity' => translate('Select City'),
            'selectArea' => translate('Select Area'),
        ],
    ]);
</script>

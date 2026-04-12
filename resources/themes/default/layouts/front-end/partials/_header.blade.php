@php($announcement = getWebConfig(name: 'announcement'))

@if (isset($announcement) && $announcement['status'] == 1)
<div class="text-center position-relative px-4 py-1 d--none" id="announcement"
    style="background-color: {{ $announcement['color'] }};color:{{$announcement['text_color']}}">
    <span>{{ $announcement['announcement'] }} </span>
    <span class="__close-announcement web-announcement-slideUp">X</span>
</div>
@endif
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    .collapse {
        visibility: visible !important;
    }

    .navbar-collapse {
        flex-grow: 0 !important;
    }

    li.nav-item.dropdown.d-none.d-md-block.active {
        border-bottom: 3px solid var(--web-primary);
        padding-bottom: 5px;
        /* thoda gap underline ke niche */
        transition: border-color 0.3s ease;
    }

    li.nav-item.dropdown.d-none.d-md-block:hover,
    li.nav-item.dropdown.d-none.d-md-block.active {
        border-color: var(--web-primary);
    }
</style>

<header class="rtl __inline-10">

    <div class="navbar-sticky bg-light mobile-head main-website-header">
        <div class="navbar navbar-expand-md navbar-light">
            <div class="container container-mobile navbar-container">
                <!-- ✅ Hamburger button -->
                <div class="align-items-center d-flex d-lg-none">
                    <button class="navbar-toggler toggle-header-mobile" id="mobile-menu-toggle">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    @php($currency_model = getWebConfig(name: 'currency_model'))
                    @if($currency_model == 'multi_currency')
                    <div class="d-none currency-main-div">
                        <div class="topbar-text dropdown disable-autohide lh-17 d-lg-none">
                            <a class="topbar-link dropdown-toggle font-lang" href="#" data-toggle="dropdown">
                                {{ getCurrencyCode(type: 'web') }} {{ getCurrencySymbol(currencyCode: getCurrencyCode(type: 'web'), type: 'web') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-{{Session::get('direction') === 'rtl' ? 'right' : 'left' }} min-width-160px">
                                @foreach (\App\Models\Currency::where('status', 1)->get() as $key => $currency)
                                <li class="dropdown-item cursor-pointer get-currency-change-function" data-code="{{$currency['code']}}">
                                    {{ $currency->name }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Logo for desktop -->
                <a class="navbar-brand d-none d-sm-block flex-shrink-0 __min-w-7rem" href="{{route('home')}}">
                    <img class="__inline-11" src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}" alt="{{$web_config['company_name']}}">
                </a>
                <!-- Logo for mobile -->
                <a class="navbar-brand d-sm-none" href="{{route('home')}}">
                    <img class="mobile-logo-img" src="{{ getStorageImages(path: $web_config['mob_logo'], type: 'logo') }}" alt="{{$web_config['company_name']}}" />
                </a>

                <!-- ✅ Navbar menu - closed by default -->
                <div class="navbar-collapse collapse" id="navbarCollapse">
                    <div class="w-100 d-lg-none text-align-direction">
                        <!-- ✅ Close button -->
                        <button class="navbar-toggler p-0" id="mobile-menu-close">
                            <i class="tio-clear __text-26px"></i>
                        </button>
                    </div>

                    <!-- Mobile menu links -->
                    <ul class="navbar-nav d-block d-md-none">
                        <li class="nav-item dropdown {{request()->is('/') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('home')}}">{{ translate('home') }}</a>
                        </li>
                        <li class="nav-item dropdown {{request()->is('/store') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('store')}}">{{ translate('Store') }}</a>
                        </li>
                        <li class="nav-item dropdown {{request()->is('/our-products') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('showcase-products')}}">{{ translate('Product') }}</a>
                        </li>
                        @if(getWebConfig('services') == 1)
                        <li class="nav-item dropdown {{request()->is('/our-services') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('showcase-services')}}">{{ translate('Services') }}</a>
                        </li>
                        @endif

                        <li class="nav-item dropdown {{request()->is('/about') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('about-us')}}">{{ translate('About Us') }}</a>
                        </li>
                        @if(getWebConfig('blog_feature_active_status') == 1)
                        <li class="nav-item dropdown {{request()->is('/blog') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('frontend.blog.index')}}">{{ translate('Blog') }}</a>
                        </li>
                        @endif

                        <li class="nav-item dropdown {{request()->is('/career') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('career')}}">{{ translate('Career') }}</a>
                        </li>
                        <li class="nav-item dropdown {{request()->is('/contacts') ? 'active' : ''}}">
                            <a class="nav-link font-semibold" href="{{route('contacts')}}">{{ translate('Contact Us') }}</a>
                        </li>
                    </ul>

                    <!-- Desktop menu links -->
                    <ul class="font-size-lg navbar-nav navbar-nav-header">
                        <li class="nav-item dropdown d-none d-md-block {{request()->is('/') ? 'active' : ''}}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{route('home')}}">{{ translate('Home')}}</a>
                        </li>
                        <li class="nav-item dropdown d-none d-md-block {{request()->is('store') ? 'active' : ''}}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{route('store')}}">{{ translate('Store')}}</a>
                        </li>
                        <li class="nav-item dropdown d-none d-md-block {{request()->is('our-products') ? 'active' : ''}}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{route('showcase-products')}}">{{ translate('Product')}}</a>
                        </li>
                        @if(getWebConfig('services') == 1)
                        <li class="nav-item dropdown d-none d-md-block {{request()->is('our-services') ? 'active' : ''}}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{route('showcase-services')}}">{{ translate('Services')}}</a>
                        </li>
                        @endif
                        <li class="nav-item dropdown d-none d-md-block {{request()->is('about-us') ? 'active' : ''}}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{route('about-us')}}">{{ translate('About Us')}}</a>
                        </li>
                        @if(getWebConfig('blog_feature_active_status') == 1)
                        <li class="nav-item dropdown d-none d-md-block {{ request()->is('blog') ? 'active' : '' }}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{ route('frontend.blog.index') }}">
                                {{ translate('Blog') }}
                            </a>
                        </li>
                        @endif

                        <li class="nav-item dropdown d-none d-md-block {{request()->is('career') ? 'active' : ''}}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{route('career')}}">{{ translate('Career')}}</a>
                        </li>
                        <li class="nav-item dropdown d-none d-md-block {{request()->is('contacts') ? 'active' : ''}}">
                            <a class="nav-link font-semibold dropdown-toggle navbar-top" href="{{route('contacts')}}">{{ translate('Contact Us')}}</a>
                        </li>
                    </ul>
                </div>

                <!-- Right side items like language/currency -->
                <div class="d-flex align-items-center header-lang-cu justify-content-end">
                    @php($currency_model = getWebConfig(name: 'currency_model'))
                    @if($currency_model == 'multi_currency')
                    <div class="d-none currency-main-div">
                        <div class="topbar-text dropdown disable-autohide me-4 lh-17 d-none d-lg-block">
                            <a class="topbar-link dropdown-toggle font-lang" href="#" data-toggle="dropdown">
                                {{ getCurrencyCode(type: 'web') }} {{ getCurrencySymbol(currencyCode: getCurrencyCode(type: 'web'), type: 'web') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-{{Session::get('direction') === 'rtl' ? 'right' : 'left' }} min-width-160px">
                                @foreach (\App\Models\Currency::where('status', 1)->get() as $key => $currency)
                                <li class="dropdown-item cursor-pointer get-currency-change-function" data-code="{{$currency['code']}}">
                                    {{ $currency->name }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    
                    <div class="topbar-text dropdown disable-autohide __language-bar text-capitalize">
                        <a class="topbar-link dropdown-toggle d-flex align-items-center font-lang" href="#" data-toggle="dropdown">
                            @foreach($web_config['language'] as $data)
                            @if($data['code'] == getDefaultLanguage())
                            <img class="me-2" width="20" src="{{theme_asset(path: 'public/assets/front-end/img/flags/' . getLanguageFlagCode($data) . '.png')}}" alt="{{$data['name']}}">
                            {{$data['name']}}
                            @endif
                            @endforeach
                        </a>
                        <ul class="dropdown-menu dropdown-lang dropdown-menu-{{Session::get('direction') === 'rtl' ? 'right' : 'left' }}">
                            @foreach($web_config['language'] as $key => $data)
                            @if($data['status'] == 1)
                            <li class="change-language" data-action="{{route('change-language')}}" data-language-code="{{$data['code']}}">
                                <a class="dropdown-item pb-1 d-flex align-items-center" href="javascript:">
                                    <img class="me-2" width="20" src="{{theme_asset(path: 'public/assets/front-end/img/flags/' . getLanguageFlagCode($data) . '.png')}}" alt="{{$data['name']}}" />
                                    <span class="text-capitalize">{{$data['name']}}</span>
                                </a>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>



</header>

@push('script')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const menuClose = document.getElementById('mobile-menu-close');
        const navbarCollapse = document.getElementById('navbarCollapse');

        if (!menuToggle || !menuClose || !navbarCollapse) return;

        // Close menu on load
        navbarCollapse.classList.remove('show');

        // Open menu
        menuToggle.addEventListener('click', function() {
            navbarCollapse.classList.add('show');
        });

        // Close menu
        menuClose.addEventListener('click', function() {
            navbarCollapse.classList.remove('show');
        });
    });
</script>
<script>
    "use strict";

    $(".category-menu").find(".mega_menu").parents("li")
        .addClass("has-sub-item").find("> a")
        .append("<i class='czi-arrow-{{Session::get('direction') === "
            rtl " ? 'left' : 'right'}}'></i>");
</script>
@endpush

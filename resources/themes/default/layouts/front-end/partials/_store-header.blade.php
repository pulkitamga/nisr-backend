<style>
    @media (min-width: 992px) {
        .main-website-header {
            position: static !important;
        }

        .store-header {
            position: sticky;
            top: 0;
            left: 0;
            z-index: 99;
        }

        .store-header-search {
            position: sticky;
            top: 79px;
            left: 0;
            z-index: 5;
        }
    }

    .currency-main-div {
        display: block !important;
    }

    .toggle-header-mobile {
        display: none;
    }

    .store-header-search {
        opacity: 0;
        max-height: 0;
        overflow: visible;
        transition: all 0.2s linear;
    }

    .store-header-search.active {
        opacity: 1;
        max-height: 500px;
    }

    .search-bar-input-color {
        border-color: ;
    }

    #desktop-search-bar {
        background-image: url('{{ asset("assets/front-end/img/background-bg-search.png") }}');
        background-size: 100% 100%;
        background-repeat: no-repeat;
        margin-top: -2px;
    }

    @media (max-width: 991.98px) {
        #desktop-search-bar {
            background-image: url('{{ asset("assets/front-end/img/search-bg-mobile.png") }}');
        }
    }

    /* Mobile devices */
    @media (max-width: 767.98px) {
        #desktop-search-bar {
            background-image: none !important;
            background-color: #ffffff;

        }

        .search-bar-input-color {
            border-color: #00423c;
        }

        .font-bold-on-mobile {
            font-weight: 600;
        }



    }
</style>


<div class="navbar navbar-expand-md navbar-stuck-menu navbar-sticky store-header">
    <div class="container px-10px">

        <!-- <div class="navbar-collapse text-align-direction" id="navbarCollapse"> -->
        <div class="collapse navbar-collapse text-align-direction d-lg-block" id="navbarCollapse">

            <div class="w-100 d-lg-none text-align-direction">
                <button class="navbar-toggler p-0" type="button" data-toggle="collapse" data-target="#navbarCollapse">
                    <i class="tio-clear __text-26px"></i>
                </button>
            </div>

            @php($categories = \App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataLimit:
            11))

            <ul class="navbar-nav mega-nav pe-lg-2 ps-lg-2 me-2 d-none d-lg-block __mega-nav custom-category-width">
                <li class="nav-item {{!request()->is('/')?'dropdown':''}} ">

                    <a class="nav-link dropdown-toggle category-menu-toggle-btn ps-0 store-category" href="javascript:">
                        <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M9.875 12.9195C9.875 12.422 9.6775 11.9452 9.32563 11.5939C8.97438 11.242 8.4975 11.0445 8 11.0445C6.75875 11.0445 4.86625 11.0445 3.625 11.0445C3.1275 11.0445 2.65062 11.242 2.29937 11.5939C1.9475 11.9452 1.75 12.422 1.75 12.9195V17.2945C1.75 17.792 1.9475 18.2689 2.29937 18.6202C2.65062 18.972 3.1275 19.1695 3.625 19.1695H8C8.4975 19.1695 8.97438 18.972 9.32563 18.6202C9.6775 18.2689 9.875 17.792 9.875 17.2945V12.9195ZM19.25 12.9195C19.25 12.422 19.0525 11.9452 18.7006 11.5939C18.3494 11.242 17.8725 11.0445 17.375 11.0445C16.1337 11.0445 14.2413 11.0445 13 11.0445C12.5025 11.0445 12.0256 11.242 11.6744 11.5939C11.3225 11.9452 11.125 12.422 11.125 12.9195V17.2945C11.125 17.792 11.3225 18.2689 11.6744 18.6202C12.0256 18.972 12.5025 19.1695 13 19.1695H17.375C17.8725 19.1695 18.3494 18.972 18.7006 18.6202C19.0525 18.2689 19.25 17.792 19.25 17.2945V12.9195ZM16.5131 9.66516L19.1206 7.05766C19.8525 6.32578 19.8525 5.13828 19.1206 4.4064L16.5131 1.79891C15.7813 1.06703 14.5937 1.06703 13.8619 1.79891L11.2544 4.4064C10.5225 5.13828 10.5225 6.32578 11.2544 7.05766L13.8619 9.66516C14.5937 10.397 15.7813 10.397 16.5131 9.66516ZM9.875 3.54453C9.875 3.04703 9.6775 2.57015 9.32563 2.2189C8.97438 1.86703 8.4975 1.66953 8 1.66953C6.75875 1.66953 4.86625 1.66953 3.625 1.66953C3.1275 1.66953 2.65062 1.86703 2.29937 2.2189C1.9475 2.57015 1.75 3.04703 1.75 3.54453V7.91953C1.75 8.41703 1.9475 8.89391 2.29937 9.24516C2.65062 9.59703 3.1275 9.79453 3.625 9.79453H8C8.4975 9.79453 8.97438 9.59703 9.32563 9.24516C9.6775 8.89391 9.875 8.41703 9.875 7.91953V3.54453Z"
                                fill="currentColor" />
                        </svg>
                        <span class="category-menu-toggle-btn-text">
                            {{ translate('categories')}}
                        </span>
                    </a>

                    <ul class="dropdown-menu __dropdown-menu-2 text-align-direction">
                        @php($categoryIndex=0)
                        @foreach($categories as $category)
                        @php($categoryIndex++)
                        @if($categoryIndex < 10) <li class="dropdown-submenu">
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{productRoute( ['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}">
                                <img width="20" class="rounded-circle"
                                    src="{{ getStorageImages(path: $category?->icon_full_url, type: 'category') }}"
                                    alt="{{ $category['name'] }}">
                                <span>{{ $category['name'] }}</span>
                            </a>

                            @if ($category->childes->count() > 0)
                            <ul class="dropdown-menu">
                                @foreach($category['childes'] as $subCategory)
                                <li>
                                    <a class="dropdown-item"
                                        href="{{productRoute( ['sub_category_id'=> $subCategory['id'],'data_from'=>'category','page'=>1])}}">
                                        {{ $subCategory['name'] }}
                                    </a>

                                    @if ($subCategory->childes->count() > 0)
                                    <ul class="dropdown-menu">
                                        @foreach($subCategory['childes'] as $subSubCategory)
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{productRoute( ['sub_sub_category_id'=> $subSubCategory['id'],'data_from'=>'category','page'=>1])}}">
                                                {{ $subSubCategory['name'] }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                            @endif
                </li>
                @endif
                @endforeach

                <li class="dropdown-divider"></li>
                <li><a class="dropdown-item text-primary"
                        href="{{ route('categories') }}">{{ translate('view_more') }}</a></li>
            </ul>
            </li>
            </ul>

            <ul class="navbar-nav navbar-desktop">
                <li class="nav-item dropdown d-lg-none">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile" href="{{ route('home') }}">{{
                        translate('Home') }}</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile" href="{{ route('store') }}">{{
                        translate('store') }}</a>
                </li>
              
                <li class="nav-item">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile" href="{{ productRoute() }}">{{
                        translate('product') }}</a>
                </li>
                @if(getWebConfig('services') == 1)
                <li class="nav-item dropdown">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile" href="{{ route('services') }}">{{
                        translate('services') }}</a>
                </li>

                @endif

                @if(
                count(getFeaturedDealsProductList()) > 0 &&
                !(($web_config['flash_deals'] || count($web_config['flash_deals_products']) > 0) || $web_config['discount_product'] > 0 || $web_config['clearance_sale_product_count'] > 0))
                <li class="nav-item dropdown">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile"
                        href="{{ productRoute( ['offer_type'=>'featured_deal']) }}">
                        {{ translate('featured_Deal')}}
                    </a>
                </li>
                @elseif(
                ($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0) &&
                !(count(getFeaturedDealsProductList()) > 0 || $web_config['discount_product'] > 0 || $web_config['clearance_sale_product_count'] > 0)
                )
                <li class="nav-item dropdown">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile"
                        href="{{ route('flash-deals', [$web_config['flash_deals']['id'] ?? 0]) }}">
                        {{ translate('flash_deal')}}
                    </a>
                </li>
                @elseif(
                ($web_config['discount_product'] > 0) &&
                !(count(getFeaturedDealsProductList()) > 0 || ($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0) || $web_config['clearance_sale_product_count'] > 0)
                )
                <li class="nav-item dropdown">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile"
                        href="{{ productRoute(  ['offer_type' => 'discounted', 'page' => 1]) }}">
                        {{ translate('discounted_products')}}
                    </a>
                </li>
                @elseif(
                ($web_config['clearance_sale_product_count'] > 0) &&
                !(count(getFeaturedDealsProductList()) > 0 || ($web_config['flash_deals'] || count($web_config['flash_deals_products']) > 0) || $web_config['discount_product'] > 0)
                )
                <li class="nav-item dropdown">
                    <a class="nav-link store-link text-lg-nowrap font-bold-on-mobile"
                        href="{{ productRoute(  ['offer_type' => 'clearance_sale', 'page' => 1]) }}">
                        {{ translate('clearance_Sale')}}
                    </a>
                </li>
                @elseif(count(getFeaturedDealsProductList()) > 0 || ($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0) || $web_config['discount_product'] > 0 || $web_config['clearance_sale_product_count'] > 0)
                <li class="nav-item">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle text-white text-max-md-dark text-capitalize ps-2 dropdown-store padd-dropdown store-link-ar"
                            type="button" id="dropdownMenuButton"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ translate('offers')}}
                        </button>
                        <div class="dropdown-menu __dropdown-menu-3 __min-w-165px text-align-direction"
                            aria-labelledby="dropdownMenuButton">
                            @if(count(getFeaturedDealsProductList()) > 0)
                            <a class="dropdown-item text-nowrap text-capitalize font-bold-on-mobile" href="{{ productRoute( ['offer_type'=>'featured_deal']) }}">
                                {{ translate('featured_Deal')}}
                            </a>
                            @endif

                            @if($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0)
                            @if(count(getFeaturedDealsProductList()) > 0)
                            <div class="dropdown-divider"></div>
                            @endif
                            <a class="dropdown-item text-nowrap text-capitalize font-bold-on-mobile" href="{{ route('flash-deals',[ $web_config['flash_deals']['id'] ?? 0]) }}">
                                {{ translate('flash_deal')}}
                            </a>
                            @endif

                            @if($web_config['discount_product'] > 0)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-nowrap text-capitalize font-bold-on-mobile" href="{{ productRoute(  ['offer_type' => 'discounted', 'page' => 1]) }}">
                                {{ translate('discounted_products')}}
                            </a>
                            @endif

                            @if($web_config['clearance_sale_product_count'] > 0)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-nowrap font-bold-on-mobile" href="{{ productRoute(  ['offer_type' => 'clearance_sale', 'page' => 1]) }}">
                                {{ translate('clearance_Sale')}}
                            </a>
                            @endif

                        </div>
                    </div>
                </li>
                @endif
                @if(getWebConfig(name: 'product_brand'))
                <li class="nav-item dropdown">
                    <a class="nav-link store-link dropdown-toggle font-bold-on-mobile" href="#" data-toggle="dropdown">{{
                        translate('brand') }}</a>
                    <ul class="text-align-direction dropdown-menu __dropdown-menu-sizing dropdown-menu-{{Session::get('direction') === "
                        rtl" ? 'right' : 'left' }} scroll-bar">
                        @php($brandIndex=0)
                        @foreach(\App\Utils\BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting()
                        as $brand)
                        @php($brandIndex++)
                        @if($brandIndex < 10) <li class="__inline-17">
                            <div>
                                <a class="dropdown-item"
                                    href="{{productRoute( ['brand_id'=> $brand['id'],'data_from'=>'brand','page'=>1])}}">
                                    {{$brand['name']}}
                                </a>
                            </div>
                            <div class="align-baseline">
                                @if($brand['brand_products_count'] > 0 )
                                <span class="count-value px-2">( {{ $brand['brand_products_count'] }}
                                    )</span>
                                @endif
                            </div>
                </li>
                @endif
                @endforeach
                <li class="__inline-17">
                    <div>
                        <a class="dropdown-item web-text-primary" href="{{route('brands')}}">
                            {{ translate('view_more') }}
                        </a>
                    </div>
                </li>
            </ul>
            </li>
            @endif
            @php($businessMode = getWebConfig(name: 'business_mode'))
            @if ($businessMode == 'multi')
            <li class="nav-item dropdown {{request()->is('/')?'active':''}}">
                <a class="nav-link store-link text-capitalize" href="{{route('vendors')}}">{{
                    translate('all_vendors')}}</a>
            </li>
            @endif

            @if(auth('customer')->check())
            <li class="nav-item d-md-none">
                <a href="{{route('user-account')}}" class="nav-link store-link text-capitalize font-bold-on-mobile">
                    {{ translate('user_profile')}}
                </a>
            </li>
            <li class="nav-item d-md-none">
                <a href="{{route('wishlists')}}" class="nav-link store-link font-bold-on-mobile">
                    {{ translate('Wishlist')}}
                </a>
            </li>
            @else
            <li class="nav-item d-md-none">
                <a class="dropdown-item ps-2 font-bold-on-mobile" href="{{route('customer.auth.login')}}">
                    <i class="fa fa-sign-in me-2"></i> {{ translate('sign_in')}}
                </a>
                <div class="dropdown-divider"></div>
            </li>
            <li class="nav-item d-md-none">
                <a class="dropdown-item ps-2 font-bold-on-mobile" href="{{route('customer.auth.sign-up')}}">
                    <i class="fa fa-user-circle me-2"></i>{{ translate('sign_up')}}
                </a>
            </li>
            @endif
            @if ($businessMode == 'multi')
            @if(getWebConfig(name: 'seller_registration'))
            <li class="nav-item">
                <div class="dropdown">
                    <button class="btn dropdown-toggle text-white text-max-md-dark text-capitalize ps-2" type="button"
                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ translate('vendor_zone')}}
                    </button>
                    <div class="dropdown-menu __dropdown-menu-3 __min-w-165px text-align-direction"
                        aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item text-nowrap text-capitalize"
                            href="{{route('vendor.auth.registration.index')}}">
                            {{ translate('become_a_vendor')}}
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-nowrap" href="{{route('vendor.auth.login')}}">
                            {{ translate('vendor_login')}}
                        </a>
                    </div>
                </div>
            </li>
            @endif
            @endif
            <li class="nav-item dropdown d-lg-none">
                <a class="nav-link dropdown-toggle text-capitalize text-lg-nowrap store-link font-bold-on-mobile" href="#" id="wholesalerDropdown"
                    role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ translate('wholesaler_zone')}}
                </a>
                <div class="dropdown-menu __dropdown-menu-3 __min-w-165px text-align-direction"
                    aria-labelledby="wholesalerDropdown">
                    <a class="dropdown-item text-nowrap text-capitalize"
                        href="{{route('wholesaler.auth.registration.index')}}">
                        {{ translate('become_a_wholesaler')}}
                    </a>
                    @if (!auth('customer')->check())

                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item text-nowrap" href="{{route('wholesaler.auth.login')}}">
                        {{ translate('wholesaler_login')}}
                    </a>
                    @endif

                </div>

            </li>




            <li class="nav-item d-lg-none">
                <a class="btn direction-ltr nev-link ps-2 text-lg-nowrap text-start text-white store-link store-phone font-bold-on-mobile" href="tel: {{ $web_config['phone'] }}">
                    <i class="fa fa-phone"></i> {{ $web_config['phone'] }}
                </a>
            </li>

            </ul>
            @if(auth('customer')->check())
            <div class="logout-btn mt-auto d-md-none">
                <hr>
                <a href="{{route('customer.auth.logout')}}" class="nav-link font-bold-on-mobile">
                    <strong class="text-base">{{ translate('logout')}}</strong>
                </a>
            </div>
            @endif



            <div class="topbar-text dropdown d-md-none ms-auto text-white">
                <a class="topbar-link d-none d-md-inline-block direction-ltr text-white font-bold-on-mobile"
                    href="tel:{{ $web_config['phone'] }}">
                    <i class="fa fa-phone"></i> {{ $web_config['phone'] }}
                </a>
            </div>
        </div>

        <!-- <div class="navbar-collapse text-align-direction">
            <ul class="navbar-nav">
                <li class="nav-item dropdown d-none d-md-block">
                    <a class="nav-link dropdown-toggle text-capitalize text-lg-nowrap" href="#" id="wholesalerDropdown"
                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ translate('wholesaler_zone')}}
                    </a>
                    <div class="dropdown-menu __dropdown-menu-3 __min-w-165px text-align-direction"
                        aria-labelledby="wholesalerDropdown">
                        <a class="dropdown-item text-nowrap text-capitalize"
                            href="{{route('wholesaler.auth.registration.index')}}">
                            {{ translate('become_a_wholesaler')}}
                        </a>
                        @if (!auth('customer')->check())

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item text-nowrap" href="{{route('wholesaler.auth.login')}}">
                            {{ translate('wholesaler_login')}}
                        </a>
                        @endif

                    </div>

                </li>

            </ul>

            <ul class="navbar-nav">
                <li class="nav-item d-none d-md-block">
                    <a class="nev-link direction-ltr text-white text-lg-nowrap" href="tel: {{ $web_config['phone'] }}">
                        <i class="fa fa-phone"></i> {{ $web_config['phone'] }}
                    </a>
                </li>
            </ul>

            <div class="topbar-text dropdown d-md-none ms-auto text-white">
                <a class="topbar-link d-none d-md-inline-block direction-ltr text-white"
                    href="tel:{{ $web_config['phone'] }}">
                    <i class="fa fa-phone"></i> {{ $web_config['phone'] }}
                </a>
            </div>

        </div> -->
        <div class="store-navbar-mobile">
            <button class="bg-transparent d-lg-none navbar-light navbar-toggler store-toggler p-0" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class='navbar-toggler-icon'></span>
            </button>
            <div class="navbar-toolbar d-flex flex-shrink-0 align-items-center">
                <a class="navbar-tool navbar-stuck-toggler" href="#">
                    <span class="navbar-tool-tooltip">{{ translate('expand_Menu') }}</span>
                    <div class="navbar-tool-icon-box">
                        <i class="navbar-tool-icon czi-menu open-icon"></i>
                        <i class="navbar-tool-icon czi-close close-icon"></i>
                    </div>
                </a>
                <div class="nav-item dropdown d-none d-lg-block">
                    <a class="nav-link dropdown-toggle text-capitalize text-lg-nowrap store-link-right font-bold-on-mobile" href="#" id="wholesalerDropdown"
                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ translate('wholesaler_zone')}}
                    </a>
                    <div class="dropdown-menu __dropdown-menu-3 __min-w-165px text-align-direction"
                        aria-labelledby="wholesalerDropdown">
                        <a class="dropdown-item text-nowrap text-capitalize"
                            href="{{route('wholesaler.auth.registration.index')}}">
                            {{ translate('become_a_wholesaler')}}
                        </a>
                        @if (!auth('customer')->check())

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item text-nowrap" href="{{route('wholesaler.auth.login')}}">
                            {{ translate('wholesaler_login')}}
                        </a>
                        @endif
                    </div>
                </div>




                <div class="nav-item d-none d-lg-block">
                    <a class="btn direction-ltr nev-link ps-2 text-lg-nowrap text-start text-white store-link-right font-bold-on-mobile" href="tel: {{ $web_config['phone'] }}">
                        <i class="fa fa-phone"></i> {{ $web_config['phone'] }}
                    </a>
                </div>
                <div class="navbar-tool desktop-search-toggl-icon open-search-form-toggle   {{ Session::get('direction') === 'rtl' ? '' : '' }}">
                    <a class="navbar-tool-icon-box bg-secondary font-bold-on-mobile" href="javascript:void(0)" id="desktop-search-toggle">
                        <i class="tio-search"></i>
                    </a>
                </div>

                    <?php if (auth('customer')->check()) {
                    ($notifications = \App\Utils\Notifications::getUserNotifications(auth('customer')->id())); ?>
                    <div class="dropdown">
                        <a class="navbar-tool " type="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <div class="navbar-tool-icon-box bg-secondary">
                                <div class="navbar-tool-icon-box bg-secondary">
                                    <i class="navbar-tool-icon czi-bell"></i>

                                    <span class="navbar-tool-label"><span class="countWishlist">{{
                                        $notifications->count() != 0 ? $notifications->count() : 0
                                        }}</span></span>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu notify dropdown-menu-{{Session::get('direction') === "rtl" ? 'left'
                        : 'right' }}" aria-labelledby="dropdownMenuButton">
                            <?php
                            if ($notifications->count() == 0) {
                                echo "<a href='#' class='dropdown-item px-2 py-1 text-center'>{{ __('No Notifications Found') }}</a>";
                            }
                            foreach ($notifications as $notification):  ?>
                                <a href="{{ route('notification.view', $notification->id) }}" class="dropdown-item px-2 py-1">
                                    <span>{{ Str::limit($notification->title, 42, '...') }}</span><br>
                                    <span style="font-size: 12px; color:#6e717a">{{ Str::limit($notification->message,
                                48, '...') }}</span><br>
                                    <span class="text-muted text-sm" style="font-size: 12px;"><i class="tio-time"></i>
                                        <?php
                                        if (!empty($notification->created_at)) {
                                            $createdAt = \Carbon\Carbon::parse($notification->created_at);
                                            echo ($createdAt->diffInDays(now()) < 7 ? $createdAt->format('D h:i A') : $createdAt->format('d M Y h:i A'));
                                        } else {
                                            echo "N/A";
                                        }
                                        ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                            <a class="dropdown-item text-center" href="{{route('notifications')}}">
                                <span class="text-truncate pe-2" title="Settings">{{
                                translate('See_all_notifications')}}</span>
                            </a>
                        </div>
                    </div>
                <?php } ?>
                <!-- end  -->
                @if(auth('customer')->check())
                <div class="navbar-tool dropdown  {{Session::get('direction') === "rtl" ? '' : '' }}">
                    <a class="navbar-tool-icon-box bg-secondary dropdown-toggle font-bold-on-mobile" href="{{route('wishlists')}}">
                        <span class="navbar-tool-label">
                            <span class="countWishlist">
                                {{session()->has('wish_list')?count(session('wish_list')):0}}
                            </span>
                        </span>
                        <i class="navbar-tool-icon czi-heart"></i>
                    </a>
                </div>
                @endif
                @if(auth('customer')->check())
                <div class="dropdown">
                    <a class="navbar-tool" type="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <div class="navbar-tool-icon-box bg-secondary">
                            <div class="navbar-tool-icon-box bg-secondary">
                                <img class="img-profile rounded-circle __inline-14" alt=""
                                    src="{{ getStorageImages(path: auth('customer')->user()->image_full_url, type: 'avatar') }}">
                            </div>
                        </div>
                        <div class="navbar-tool-text text-white">
                            <small class=" text-white">
                                {{ translate('hello')}}, {{ Str::limit(auth('customer')->user()->f_name, 10) }}
                            </small>
                            {{ translate('dashboard')}}
                        </div>
                    </a>
                    <div class="dropdown-menu __auth-dropdown dropdown-menu-{{Session::get('direction') === "rtl" ? 'left' : 'right'
                        }}" aria-labelledby="dropdownMenuButton">
                        @if(auth('customer')->check())
                        @if(auth('customer')->user()->wholesaler_status == 1)
                        <a class="dropdown-item " href="{{route('wholesale.account.order')}}"> {{
                            translate('my_Order')}} </a>
                        @endif

                        <a class="dropdown-item " href="{{route('user-account')}}"> {{
                            translate('my_Profile')}}</a>

                        @endif
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{route('customer.auth.logout')}}">{{
                            translate('logout')}}</a>
                    </div>
                </div>
                @else
                <div class="dropdown ">
                    <a class="navbar-tool {{Session::get('direction') === "rtl" ? 'me-md-2' : '' }}"
                        type="button" data-toggle="dropdown" aria-haspopup="true" href="#" rel="nofollow"
                        aria-expanded="false">
                        <div class="navbar-tool-icon-box bg-secondary">
                            <div class="navbar-tool-icon-box bg-secondary">
                                <i class="navbar-tool-icon czi-user"></i>
                            </div>
                        </div>
                    </a>
                    <div class="text-align-direction dropdown-lang dropdown-menu  dropdown-menu-{{Session::get('direction') === "
                        rtl" ? 'left' : 'right' }}" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="{{route('customer.auth.login')}}">
                            <i class="fa fa-sign-in me-2"></i> {{ translate('sign_in')}}
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{route('customer.auth.sign-up')}}">
                            <i class="fa fa-user-circle me-2"></i>{{ translate('sign_up')}}
                        </a>
                    </div>
                </div>
                @endif
                <div id="cart_items">
                    @include('layouts.front-end.partials._cart')
                </div>
            </div>
        </div>
    </div>

</div>


<div id="desktop-search-bar" class="navbar navbar-expand-md p-3 store-header-search active">

    <div class="container">
        <div class="input-group-overlay text-align-direction">
            <form action="{{ productRoute() }}" method="GET" class="search_form">
                <div class="d-flex align-items-center gap-2">
                    <input class="form-control appended-form-control search-bar-input search-bar-input-color" type="search"
                        autocomplete="off" data-given-value=""
                        placeholder="{{ translate("search_for_items")}}..."
                        name="name" value="{{ request('name') }}">

                    <input type="hidden" name="global_search_input" value="1">

                    <button class="input-group-append-overlay search_button d-none d-md-block" type="submit">
                        <span class="input-group-text __text-20px">
                            <i class="czi-search text-white"></i>
                        </span>
                    </button>

                    <span class="close-search-form fs-14 text-muted d-none d-md-inline" style="cursor: pointer">
                        {{ translate('cancel') }}
                    </span>
                </div>

                <input name="data_from" value="search" hidden>
                <input name="page" value="1" hidden>
                <div class="card search-card mobile-search-card">
                    <div class="card-body">
                        <div class="search-result-box __h-400px overflow-x-hidden overflow-y-auto"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<span id="update_nav_cart_url" data-url="{{route('cart.nav-cart')}}"></span>

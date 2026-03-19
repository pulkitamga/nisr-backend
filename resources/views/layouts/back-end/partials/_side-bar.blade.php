@php
    use App\Enums\ViewPaths\Admin\Brand;
    use App\Enums\ViewPaths\Admin\BusinessSettings;
    use App\Enums\ViewPaths\Admin\Category;
    use App\Enums\ViewPaths\Admin\Chatting;
    use App\Enums\ViewPaths\Admin\Currency;
    use App\Enums\ViewPaths\Admin\Customer;
    use App\Enums\ViewPaths\Admin\CustomerWallet;
    use App\Enums\ViewPaths\Admin\Dashboard;
    use App\Enums\ViewPaths\Admin\DatabaseSetting;
    use App\Enums\ViewPaths\Admin\DealOfTheDay;
    use App\Enums\ViewPaths\Admin\DeliveryMan;
    use App\Enums\ViewPaths\Admin\DeliverymanWithdraw;
    use App\Enums\ViewPaths\Admin\DeliveryRestriction;
    use App\Enums\ViewPaths\Admin\Employee;
    use App\Enums\ViewPaths\Admin\EnvironmentSettings;
    use App\Enums\ViewPaths\Admin\FeatureDeal;
    use App\Enums\ViewPaths\Admin\FeaturesSection;
    use App\Enums\ViewPaths\Admin\ClearanceSale;
    use App\Enums\ViewPaths\Admin\FlashDeal;
    use App\Enums\ViewPaths\Admin\GoogleMapAPI;
    use App\Enums\ViewPaths\Admin\HelpTopic;
    use App\Enums\ViewPaths\Admin\CrmAgentSalesMatrixReport;
    use App\Enums\ViewPaths\Admin\CrmEmployeeChannelAssignmentReport;
    use App\Enums\ViewPaths\Admin\InhouseProductSale;
    use App\Enums\ViewPaths\Admin\CrmDealSalesReport;
    use App\Enums\ViewPaths\Admin\Mail;
    use App\Enums\ViewPaths\Admin\OfflinePaymentMethod;
    use App\Enums\ViewPaths\Admin\Order;
    use App\Enums\ViewPaths\Admin\SupportTicket;
    use App\Enums\ViewPaths\Admin\Pages;
    use App\Enums\ViewPaths\Admin\Product;
    use App\Enums\ViewPaths\Admin\ExtraCharges;
    use App\Enums\ViewPaths\Admin\StockRequest;
    use App\Enums\ViewPaths\Admin\StockTransfer;
    use App\Enums\ViewPaths\Admin\PushNotification;
    use App\Enums\ViewPaths\Admin\Recaptcha;
    use App\Enums\ViewPaths\Admin\RefundRequest;
    use App\Enums\ViewPaths\Admin\SiteMap;
    use App\Enums\ViewPaths\Admin\SMSModule;
    use App\Enums\ViewPaths\Admin\SocialLoginSettings;
    use App\Enums\ViewPaths\Admin\SocialMedia;
    use App\Enums\ViewPaths\Admin\SoftwareUpdate;
    use App\Enums\ViewPaths\Admin\SubCategory;
    use App\Enums\ViewPaths\Admin\SubSubCategory;
    use App\Enums\ViewPaths\Admin\ThemeSetup;
    use App\Enums\ViewPaths\Admin\FirebaseOTPVerification;
    use App\Enums\ViewPaths\Admin\Vendor;
    use App\Enums\ViewPaths\Admin\Branch;
    use App\Enums\ViewPaths\Admin\Department;
    use App\Enums\ViewPaths\Admin\InhouseShop;
    use App\Enums\ViewPaths\Admin\SocialMediaChat;
    use App\Enums\ViewPaths\Admin\ShippingMethod;
    use App\Enums\ViewPaths\Admin\PaymentMethod;
    use App\Enums\ViewPaths\Admin\InvoiceSettings;
    use App\Enums\ViewPaths\Admin\SEOSettings;
    use App\Enums\ViewPaths\Admin\ErrorLogs;
    use App\Enums\ViewPaths\Admin\StorageConnectionSettings;
    use App\Enums\ViewPaths\Admin\SystemSetup;
    use App\Enums\ViewPaths\Admin\WholeSalesProducts;
    use App\Enums\ViewPaths\Admin\WholeSaler;
    use App\Utils\Helpers;
    use App\Utils\BranchHelper;
    use App\Enums\EmailTemplateKey;

    $eCommerceLogo = getWebConfig(name: 'company_web_logo');
    $contentPages = [
        'home' => ['label' => 'Home', 'icon' => 'tio-home'],
        'about-us' => ['label' => 'About Us', 'icon' => 'tio-info'],
        'career' => ['label' => 'Career', 'icon' => 'tio-briefcase'],
        'products' => ['label' => 'Products', 'icon' => 'tio-pen'],
        'services' => ['label' => 'Services', 'icon' => 'tio-support'],
    ];

    $brandSetting = getWebConfig(name: 'product_brand');
@endphp


<div id="sidebarMain" class="d-none">
    <aside
        class="bg-white js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered text-start">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between side-logo dashboard-navbar-side-logo-wrapper">
                <a class="navbar-brand" href="{{ route('admin.dashboard.index') }}" aria-label="Front">
                    <img class="navbar-brand-logo-mini for-web-logo max-h-30"
                        src="{{ getStorageImages(path: $eCommerceLogo, type: 'backend-logo') }}"
                        alt="{{ translate('logo') }}">
                </a>
                <button type="button"
                    class="d-none js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>

                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                        data-template="<div class=&quot;tooltip d-none d-sm-block&quot; role=&quot;tooltip&quot;><div class=&quot;arrow&quot;></div><div class=&quot;tooltip-inner&quot;></div></div>"></i>
                </button>
            </div>
            <div class="navbar-vertical-footer-offset pb-0">
                <div class="navbar-vertical-content">
                    <div class="sidebar--search-form pb-3 pt-4 mx-3">
                        <div class="search--form-group">
                            <button type="button" class="btn"><i class="tio-search"></i></button>
                            <input type="text" class="js-form-search form-control form--control"
                                id="search-bar-input" placeholder="{{ translate('search_menu') . '...' }}">
                        </div>
                    </div>
                    <ul class="navbar-nav navbar-nav-lg nav-tabs">
                        @if (Helpers::module_permission_check('dashboard'))
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/dashboard' . Dashboard::VIEW[URI]) ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    title="{{ translate('dashboard') }}" href="{{ route('admin.dashboard.index') }}">
                                    <i class="tio-home-vs-1-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('dashboard') }}
                                    </span>
                                </a>
                            </li>
                        @endif
                        @php
                            $branches = BranchHelper::getAccessibleBranches();
                        @endphp

                        @if (Helpers::module_permission_check('pos_management'))
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/pos*') ? 'active' : '' }}">
                                <a href="javascript:void(0);" onclick="handlePOSClick({{ $branches->count() }})"
                                    class="nav-link">
                                    <i class="tio-shopping nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('POS') }}</span>
                                </a>
                            </li>
                        @endif
                        <div class="modal fade" id="branchSelectModal" tabindex="-1"
                            aria-labelledby="branchSelectModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="branchSelectModalLabel">{{ translate('Select Branch') }}</h5>
                                        <button type="button" class="close custom-close" data-dismiss="modal"
                                            aria-label="Close">
                                            &times;
                                        </button>
                                    </div>
                                    <form id="posBranchForm" method="GET" action="{{ route('admin.pos.index') }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="posBranchId" class="form-label">{{ translate('Branch') }}</label>
                                                <select class="js-select2-custom form-control form-select"
                                                    id="posBranchId" name="branch_id" required>
                                                    <option value="0" selected disabled>
                                                        {{ translate('select_branch') }}
                                                    </option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary w-100">{{ translate('Go to POS') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @if (Helpers::module_permission_check('order_management'))
                            <li
                                class="nav-item {{ Request::is('admin/orders*') ? (Request::is('admin/orders/details/*') && request()->has('vendor-order-list') ? '' : 'scroll-here') : '' }}">
                                <small class="nav-subtitle" title="">{{ translate('order_management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/orders*') ? (Request::is('admin/orders/details/*') && request()->has('vendor-order-list') ? '' : 'active') : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('orders') }}">
                                    <i class="tio-shopping-cart-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('orders') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/order*') ? (Request::is('admin/orders/details/*') && request()->has('vendor-order-list') ? '' : 'block') : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/all') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.orders.list', ['all']) }}"
                                            title="{{ translate('all') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('all') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\Order::count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/pending') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.orders.list', ['pending']) }}"
                                            title="{{ translate('pending') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('pending') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\Order::where(['order_status' => 'pending'])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/confirmed') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.orders.list', ['confirmed']) }}"
                                            title="{{ translate('confirmed') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('confirmed') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\Order::where(['order_status' => 'confirmed'])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/processing') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.orders.list', ['processing']) }}"
                                            title="{{ translate('packaging') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('packaging') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\Order::where(['order_status' => 'processing'])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/out_for_delivery') ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.orders.list', ['out_for_delivery']) }}"
                                            title="{{ translate('out_for_delivery') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('out_for_delivery') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\Order::where(['order_status' => 'out_for_delivery'])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/delivered') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.orders.list', ['delivered']) }}"
                                            title="{{ translate('delivered') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('delivered') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\Order::where(['order_status' => 'delivered'])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/returned') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.orders.list', ['returned']) }}"
                                            title="{{ translate('returned') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('returned') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ \App\Models\Order::where('order_status', 'returned')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/failed') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.orders.list', ['failed']) }}"
                                            title="{{ translate('failed') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('failed_to_Deliver') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ \App\Models\Order::where(['order_status' => 'failed'])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>

                                    <li
                                        class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/canceled') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.orders.list', ['canceled']) }}"
                                            title="{{ translate('canceled') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('canceled') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ \App\Models\Order::where(['order_status' => 'canceled'])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/refund-section/refund/*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('refund_Requests') }}">
                                    <i class="tio-receipt-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('refund_Requests') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/refund-section/refund*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/refund-section/refund/' . RefundRequest::LIST[URI] . '/pending') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.refund-section.refund.list', ['pending']) }}"
                                            title="{{ translate('pending') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('pending') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ \App\Models\RefundRequest::where('status', 'pending')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>

                                    <li
                                        class="nav-item {{ Request::is('admin/refund-section/refund/' . RefundRequest::LIST[URI] . '/approved') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.refund-section.refund.list', ['approved']) }}"
                                            title="{{ translate('approved') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('approved') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\RefundRequest::where('status', 'approved')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/refund-section/refund/' . RefundRequest::LIST[URI] . '/refunded') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.refund-section.refund.list', ['refunded']) }}"
                                            title="{{ translate('refunded') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('refunded') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\RefundRequest::where('status', 'refunded')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/refund-section/refund/' . RefundRequest::LIST[URI] . '/rejected') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.refund-section.refund.list', ['rejected']) }}"
                                            title="{{ translate('rejected') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('rejected') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ \App\Models\RefundRequest::where('status', 'rejected')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (Helpers::module_permission_check('product_management'))
                            <li
                                class="nav-item {{ Request::is('admin/brand*') || Request::is('admin/category*') || Request::is('admin/sub*') || Request::is('admin/attribute*') || Request::is('admin/products*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle"
                                    title="">{{ translate('product_management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/category*') || Request::is('admin/sub-category*') || Request::is('admin/sub-sub-category*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('category_Setup') }}">
                                    <i class="tio-filter-list nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('category_Setup') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/category*') || Request::is('admin/sub*') ? 'block' : '' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/category/' . Category::LIST[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.category.view') }}"
                                            title="{{ translate('categories') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('categories') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/sub-category/' . SubCategory::LIST[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.sub-category.view') }}"
                                            title="{{ translate('sub_Categories') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('sub_Categories') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/sub-sub-category/' . SubSubCategory::LIST[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.sub-sub-category.view') }}"
                                            title="{{ translate('sub_Sub_Categories') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('sub_Sub_Categories') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @if ($brandSetting)
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/brand*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="{{ translate('brands') }}">
                                        <i class="tio-star nav-icon"></i>
                                        <span
                                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('brands') }}</span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display: {{ Request::is('admin/brand*') ? 'block' : 'none' }}">
                                        <li class="nav-item {{ Request::is('admin/brand/' . Brand::ADD[URI]) ? 'active' : '' }}"
                                            title="{{ translate('add_new') }}">
                                            <a class="nav-link " href="{{ route('admin.brand.add-new') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ translate('add_new') }}</span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/brand/' . Brand::LIST[URI]) ? 'active' : '' }}"
                                            title="{{ translate('list') }}">
                                            <a class="nav-link " href="{{ route('admin.brand.list') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ translate('list') }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/attribute*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    href="{{ route('admin.attribute.view') }}"
                                    title="{{ translate('product_Attribute_Setup') }}">
                                    <i class="tio-category-outlined nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('product_Attribute_Setup') }}</span>
                                </a>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/products/' . Product::LIST[URI] . '/in-house') ||
                                Request::is('admin/products/' . Product::BULK_IMPORT[URI]) ||
                                Request::is('admin/products/' . Product::ADD[URI]) ||
                                Request::is('admin/products/' . Product::VIEW[URI] . '/in-house/*') ||
                                Request::is('admin/products/' . Product::STOCK_LIMIT_PRODUCTS[URI]) ||
                                Request::is('admin/products/' . Product::BARCODE_GENERATE[URI] . '/*') ||
                                (Request::is('admin/products/' . Product::UPDATE[URI] . '/*') && request()->has('product-gallery'))
                                    ? 'active'
                                    : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('in-House_Products') }}">
                                    <i class="tio-shop nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        <span class="text-truncate">{{ translate('in-house_Products') }}</span>
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/products/' . Product::ADD[URI] . '/in-house') || Request::is('admin/products/' . Product::LIST[URI] . '/in-house') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::STOCK_LIMIT[URI] . '/in-house') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::BULK_IMPORT[URI]) || Request::is('admin/stock-transfer/' . StockTransfer::ADD[URI]) || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::ADD[URI]) || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::VIEW[URI] . '/in-house/*') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::BARCODE_GENERATE[URI] . '/*') || (Request::is('admin/products/' . Product::UPDATE[URI] . '/*') && request()->has('product-gallery')) ? 'block' : '' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/products/' . Product::LIST[URI] . '/in-house') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::VIEW[URI] . '/in-house/*') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::STOCK_LIMIT[URI] . '/in-house') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::BARCODE_GENERATE[URI] . '/*') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.products.list', ['in-house']) }}"
                                            title="{{ translate('Product_List') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('Product_List') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ getAdminProductsCount('all') }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/products/' . Product::ADD[URI]) || (Request::is('admin/products/' . Product::UPDATE[URI] . '/*') && request()->has('product-gallery')) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.products.add') }}"
                                            title="{{ translate('add_New_Product') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('add_New_Product') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/products/' . Product::BULK_IMPORT[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.products.bulk-import') }}"
                                            title="{{ translate('bulk_import') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('bulk_import') }}</span>
                                        </a>
                                    </li>

                                    <li
                                        class="nav-item {{ Request::is('admin/products/' . Product::STOCK_LIMIT_PRODUCTS[URI]) ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.products.stock-limit-products', ['in_house']) }}"
                                            title="{{ translate('product_stock') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('product_stock') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/products/' . Product::LIST[URI] . '/vendor*') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::VIEW[URI] . '/vendor/*') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::UPDATED_PRODUCT_LIST[URI]) ? 'active' : '' }} d-none">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('vendor_Products') }}">
                                    <i class="tio-airdrop nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('vendor_Products') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::LIST[URI] . '/vendor*') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::VIEW[URI] . '/vendor/*') || Request::is('admin/products/' . \App\Enums\ViewPaths\Admin\Product::UPDATED_PRODUCT_LIST[URI]) ? 'block' : '' }}">
                                    <li
                                        class="nav-item {{ str_contains(url()->current() . '?status=' . request()->get('status'), 'admin/products/' . \App\Enums\ViewPaths\Admin\Product::LIST[URI] . '/vendor?status=0') == 1 ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('new_Products_Requests') }}"
                                            href="{{ route('admin.products.list', ['vendor', 'status' => '0']) }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('new_Products_Requests') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ getVendorProductsCount('new-product') }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    @if (getWebConfig(name: 'product_wise_shipping_cost_approval') == 1)
                                        <li
                                            class="nav-item {{ Request::is('admin/products/' . Product::UPDATED_PRODUCT_LIST[URI]) ? 'active' : '' }}">
                                            <a class="nav-link text-capitalize"
                                                title="{{ translate('product_update_requests') }}"
                                                href="{{ route('admin.products.updated-product-list') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span
                                                    class="text-truncate text-capitalize">{{ Str::limit(translate('product_update_requests'), 18, '...') }}
                                                    <span class="badge badge-soft-info badge-pill ml-1">
                                                        {{ getVendorProductsCount('product-updated-request') }}
                                                    </span>
                                                </span>
                                            </a>
                                        </li>
                                    @endif
                                    <li
                                        class="nav-item {{ str_contains(url()->current() . '?status=' . request()->get('status'), '/admin/products/' . Product::LIST[URI] . '/vendor?status=1') == 1 ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('approved_Products') }}"
                                            href="{{ route('admin.products.list', ['vendor', 'status' => '1']) }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('approved_Products') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ getVendorProductsCount('approved') }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ str_contains(url()->current() . '?status=' . request()->get('status'), '/admin/products/' . Product::LIST[URI] . '/vendor?status=2') == 1 ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('denied_Products') }}"
                                            href="{{ route('admin.products.list', ['vendor', 'status' => '2']) }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('denied_Products') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ getVendorProductsCount('denied') }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/products/' . Product::PRODUCT_GALLERY[URI]) ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    href="{{ route('admin.products.product-gallery') }}"
                                    title="{{ translate('product_gallery') }}">
                                    <i class="tio-survey nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('product_gallery') }}</span>
                                </a>
                            </li> --}}
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/products/' . Product::PRODUCT_MAKE[URI]) ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('product_makes') }}">
                                    <i class="tio-car nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('product_make_setup') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub">
                                    <li
                                        class="nav-item {{ Request::is('admin/products/' . Product::PRODUCT_MAKE[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.products.product-make') }}"
                                            title="{{ translate('makes') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('makes') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (Helpers::module_permission_check('warranty_section'))
                            <li class="nav-item {{ Request::is('admin/warranty*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle"
                                    title="">{{ translate('warranty_management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/dashboard') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    href="{{ route('admin.warranty.dashboard') }}"
                                    title="{{ translate('warranty_dashboard') }}">
                                    <i class="tio-home-vs-1-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('warranty_dashboard') }}
                                    </span>
                                </a>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/import*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('imports') }}">
                                    <i class="tio-upload nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('imports') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/warranty/import*') ? 'block' : 'none' }}">
                                    <li class="nav-item {{ Request::is('admin/warranty/import') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.import') }}"
                                            title="{{ translate('csv_upload') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('csv_upload') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/import-history') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.import-history') }}"
                                            title="{{ translate('history') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('history') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\Warranty::where('status', 'preactivated')->distinct()->count(\Illuminate\Support\Facades\DB::raw('DATE(created_at)')) }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/activation*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('activations') }}">
                                    <i class="tio-checkmark-circle-outlined nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('activations') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/warranty/activation*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/activation/list') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.activation.list') }}"
                                            title="{{ translate('all') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('all') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\Warranty::count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/activation/manual') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.warranty.activation.manual.view') }}"
                                            title="{{ translate('manual_activate') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('manual_activate') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/review*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('reviews') }}">
                                    <i class="tio-hangouts-outlined nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('reviews') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/warranty/review*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/review/activation') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.review.activation') }}"
                                            title="{{ translate('activation_reviews') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('activation_reviews') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\ActivationReview::where('status', 'pending')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/blacklist') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    href="{{ route('admin.warranty.blacklist') }}"
                                    title="{{ translate('blacklist') }}">
                                    <i class="tio-warning nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('blacklist') }}
                                        <span class="badge badge-soft-danger badge-pill ml-1">
                                            {{ \App\Models\Blacklist::count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/serial-transaction*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    href="{{ route('admin.warranty.serial-transaction.list') }}"
                                    title="{{ translate('serial_transaction_history') }}">
                                    <i class="tio-history nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('serial_transaction_history') }}
                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            {{ \App\Models\SerialTransferHistory::count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/claim*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('warranty_Claims') }}">
                                    <i class="tio-receipt nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('warranty_Claims') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/warranty/claim*') ? 'block' : 'none' }}">
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/all') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.all') }}"
                                            title="{{ translate('all') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('all') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/new') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.new') }}"
                                            title="{{ translate('new') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('new') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'new')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/triage-pending') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.triage-pending') }}" title="{{ translate('triage_pending') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('triage_pending') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'triage_pending')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/claim/approved') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.approved') }}"
                                            title="{{ translate('approved') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('approved') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'approved')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/claim/rma-issued') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.rma-issued') }}"
                                            title="{{ translate('rma_issued') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('rma_issued') }}
                                                <span class="badge badge-soft-primary badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'rma_issued')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/claim/received') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.received') }}"
                                            title="{{ translate('received') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('received') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'received')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/claim/repair-pending') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.warranty.claim.repair-pending') }}"
                                            title="{{ translate('repair_pending') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('repair_pending') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'repair_pending')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/replacement-pending') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.replacement-pending') }}"
                                            title="{{ translate('replacement_pending') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('replacement_pending') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'replacement_pending')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/waiting-customer') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.waiting-customer') }}"
                                            title="{{ translate('waiting_customer') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('waiting_customer') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'waiting_customer')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/waiting-parts') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.waiting-parts') }}"
                                            title="{{ translate('waiting_parts') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('waiting_parts') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'waiting_parts')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/waiting-payment') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.waiting-payment') }}"
                                            title="{{ translate('waiting_payment') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('waiting_payment') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'waiting_payment')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/diagnosis-pending') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.diagnosis-pending') }}"
                                            title="{{ translate('diagnosis_pending') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('diagnosis_pending') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'diagnosis_pending')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/qc-pending') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.qc-pending') }}"
                                            title="{{ translate('qc_pending') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('qc_pending') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'qc_pending')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/shipped-ready') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.shipped-ready') }}"
                                            title="{{ translate('shipped_ready') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('shipped_ready') }}
                                                <span class="badge badge-soft-primary badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'shipped_ready')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/warranty/claim/dispatched') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.dispatched') }}"
                                            title="{{ translate('dispatched') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('dispatched') }}
                                                <span class="badge badge-soft-primary badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'dispatched')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/claim/resolved') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.resolved') }}"
                                            title="{{ translate('resolved') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('resolved') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'resolved')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/claim/closed') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.closed') }}"
                                            title="{{ translate('closed') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('closed') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'closed')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/warranty/claim/rejected') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.warranty.claim.rejected') }}"
                                            title="{{ translate('rejected') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('rejected') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ \App\Models\WarrantyClaim::where('status', 'rejected')->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (Helpers::module_permission_check('promotion_management'))
                            <li
                                class="nav-item {{ Request::is('admin/banner*') || Request::is('admin/coupon*') || Request::is('admin/notification*') || Request::is('admin/deal*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle"
                                    title="">{{ translate('promotion_management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/banner*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    href="{{ route('admin.banner.list') }}"
                                    title="{{ translate('banner_Setup') }}">
                                    <i class="tio-photo-square-outlined nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('banner_Setup') }}</span>
                                </a>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/coupon*') || Request::is('admin/deal*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('offers_&_Deals') }}">
                                    <i class="tio-users-switch nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('offers_&_Deals') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/coupon*') || Request::is('admin/deal*') ? 'block' : 'none' }}">
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/coupon*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.coupon.add') }}"
                                            title="{{ translate('coupon') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('coupon') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/deal/' . FlashDeal::LIST[URI]) || Request::is('admin/deal/' . FlashDeal::UPDATE[URI] . '*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.deal.flash') }}"
                                            title="{{ translate('flash_Deals') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('flash_Deals') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/deal/' . DealOfTheDay::LIST[URI]) || Request::is('admin/deal/' . DealOfTheDay::UPDATE[URI] . '*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.deal.day') }}"
                                            title="{{ translate('deal_of_the_day') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('deal_of_the_day') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/deal/' . FeatureDeal::LIST[URI]) || Request::is('admin/deal/' . FeatureDeal::UPDATE[URI] . '*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.deal.feature') }}"
                                            title="{{ translate('featured_Deal') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('featured_Deal') }}
                                            </span>
                                        </a>
                                    </li>

                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/deal/clearance-sale') || Request::is('admin/deal/clearance-sale*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.deal.clearance-sale.index') }}"
                                            title="{{ translate('Clearance_Sale') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Clearance_Sale') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/notification*') || Request::is('admin/push-notification/*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('notifications') }}">
                                    <i class="tio-users-switch nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('notifications') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/notification*') || Request::is('admin/push-notification/*') ? 'block' : 'none' }}">
                                    <!-- Notification list  -->

                                    <li
                                        class="navbar-vertical-aside-has-menu {{ !Request::is('admin/notification/push') && Request::is('admin/notification/*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.notification.index') }}"
                                            title="{{ translate('send_notification') }}">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/send-notification.svg') }}"
                                                alt="{{ translate('send_notification_svg') }}" width="15"
                                                class="me-2">
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                                {{ translate('send_notification') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/' . PushNotification::INDEX[URI]) || Request::is('admin/push-notification/' . PushNotification::FIREBASE_CONFIGURATION[URI]) || Request::is('admin/push-notification/' . PushNotification::INDEX[URI]) ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link text-capitalize"
                                            href="{{ route('admin.push-notification.index') }}"
                                            title="{{ translate('push_notifications_setup') }}">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/push-notification.svg') }}"
                                                alt="{{ translate('push_notification_svg') }}" width="15"
                                                class="me-2">
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                                {{ translate('push_notifications_setup') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/' . BusinessSettings::ANNOUNCEMENT[URI]) ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    href="{{ route('admin.business-settings.announcement') }}"
                                    title="{{ translate('announcement') }}">
                                    <i class="tio-mic-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('announcement') }}
                                    </span>
                                </a>
                            </li>
                        @endif

                        @if (Helpers::module_permission_check('system_settings'))
                            @if (count(config('get_theme_routes')) > 0)
                                <li
                                    class="nav-item {{ Request::is('admin/banner*') || Request::is('admin/coupon*') || Request::is('admin/notification*') || Request::is('admin/deal*') ? 'scroll-here' : '' }}">
                                    <small class="nav-subtitle"
                                        title="">{{ config('get_theme_routes')['name'] }}
                                        {{ translate('Menu') }}</small>
                                    <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                                </li>
                                @foreach (config('get_theme_routes')['route_list'] as $route)
                                    @if (isset($route['module_permission']) && Helpers::module_permission_check($route['module_permission']))
                                        <li
                                            class="navbar-vertical-aside-has-menu {{ Request::is($route['path']) || Request::is($route['path'] . '*') ? 'active' : '' }} @foreach ($route['route_list'] as $sub_route){{ Request::is($sub_route['path']) || Request::is($sub_route['path'] . '*') ? 'active' : '' }} @endforeach">
                                            <a class="js-navbar-vertical-aside-menu-link nav-link {{ count($route['route_list']) > 0 ? 'nav-link-toggle' : '' }}"
                                                href="{{ count($route['route_list']) > 0 ? 'javascript:' : $route['url'] }}"
                                                title="{{ translate($route['name']) }}">
                                                {!! $route['icon'] !!}
                                                <span
                                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate($route['name']) }}</span>
                                            </a>

                                            @if (count($route['route_list']) > 0)
                                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                                    style="display: @foreach ($route['route_list'] as $sub_route){{ Request::is($sub_route['path']) || Request::is($sub_route['path'] . '*') ? 'block' : 'none' }} @endforeach">
                                                    @foreach ($route['route_list'] as $sub_route)
                                                        <li
                                                            class="navbar-vertical-aside-has-menu {{ Request::is($sub_route['path']) || Request::is($sub_route['path'] . '*') ? 'active' : '' }}">
                                                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                                                href="{{ $sub_route['url'] }}"
                                                                title="{{ translate($sub_route['name']) }}">
                                                                <span class="tio-circle nav-indicator-icon"></span>
                                                                <span
                                                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate($sub_route['name']) }}</span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        @endif

                        @if (
                                Helpers::module_permission_check('report') ||
                                    Helpers::module_permission_check('branch_management') ||
                                    Helpers::module_permission_check('crm_section') ||
                                    Helpers::module_permission_check('wholesaler_section') ||
                                    Helpers::module_permission_check('warranty_section'))
                            <li
                                class="nav-item {{ Request::is('admin/report/*') || Request::is('admin/transaction/*') || Request::is('admin/stock/product-in-wishlist') || Request::is('admin/stock/product-stock') || Request::is('admin/stock/transfer-report*') || Request::is('admin/reports/*') || Request::is('admin/branch/sales*') || Request::is('admin/crm/sales-report*') || Request::is('admin/crm/chart-view') || Request::is('admin/warranty/report*') || Request::is('admin/wholesale/dashboard*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle" title="">
                                    {{ translate('reports_&_Analysis') }}
                                </small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>
                            @if (Helpers::module_permission_check('report'))

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/admin-earning') || Request::is('admin/report/vendor-earning') || Request::is('admin/report/' . InhouseProductSale::VIEW[URI]) || Request::is('admin/report/vendor-report') || Request::is('admin/report/earning') || Request::is('admin/transaction/order-transaction-list') || Request::is('admin/transaction/expense-transaction-list') || Request::is('admin/report/transaction/' . App\Enums\ViewPaths\Admin\RefundTransaction::INDEX[URI]) || Request::is('admin/transaction/wallet-bonus') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="Sales">
                                    <i class="tio-chart-bar-4 nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        Sales
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/report/admin-earning') || Request::is('admin/report/vendor-earning') || Request::is('admin/report/' . InhouseProductSale::VIEW[URI]) || Request::is('admin/report/vendor-report') || Request::is('admin/report/earning') || Request::is('admin/transaction/order-transaction-list') || Request::is('admin/transaction/expense-transaction-list') || Request::is('admin/report/transaction/' . App\Enums\ViewPaths\Admin\RefundTransaction::INDEX[URI]) || Request::is('admin/transaction/wallet-bonus') ? 'block' : 'none' }}">
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/admin-earning') || Request::is('admin/report/vendor-earning') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.report.admin-earning') }}"
                                            title="{{ translate('Earning_Reports') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Earning_Reports') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/report/' . InhouseProductSale::VIEW[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.report.inhouse-product-sale') }}"
                                            title="{{ translate('inhouse_Sales') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('inhouse_Sales') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/report/vendor-report') ? 'active' : '' }} d-none">
                                        <a class="nav-link" href="{{ route('admin.report.vendor-report') }}"
                                            title="{{ translate('vendor_Sales') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">
                                                {{ translate('vendor_Sales') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/transaction/order-transaction-list') || Request::is('admin/transaction/expense-transaction-list') || Request::is('admin/transaction/refund-transaction-list') || Request::is('admin/report/transaction/' . App\Enums\ViewPaths\Admin\RefundTransaction::INDEX[URI]) || Request::is('admin/transaction/wallet-bonus') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.transaction.order-transaction-list') }}"
                                            title="{{ translate('transaction_Report') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('transaction_Report') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/order') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="Orders">
                                    <i class="tio-shopping-cart-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        Orders
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/report/order') ? 'block' : 'none' }}">
                                    <li class="nav-item {{ Request::is('admin/report/order') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.report.order') }}"
                                            title="{{ translate('order_Report') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('order_Report') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/all-product') || Request::is('admin/report/order') || Request::is('admin/stock/product-in-wishlist') || Request::is('admin/stock/product-stock') || Request::is('admin/products/' . Product::STOCK_LIMIT_PRODUCTS[URI] . '*') || Request::is('admin/products/' . Product::STOCK_REPORT[URI] . '*') || Request::is('admin/products/' . Product::REQUEST_RESTOCK_LIST[URI]) ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="Products">
                                    <i class="tio-chart-bar-4 nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        Products
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/report/all-product') || Request::is('admin/report/order') || Request::is('admin/stock/product-in-wishlist') || Request::is('admin/stock/product-stock') || Request::is('admin/products/' . Product::STOCK_LIMIT_PRODUCTS[URI] . '*') || Request::is('admin/products/' . Product::STOCK_REPORT[URI] . '*') || Request::is('admin/products/' . Product::REQUEST_RESTOCK_LIST[URI]) ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/report/all-product') || Request::is('admin/stock/product-in-wishlist') || Request::is('admin/stock/product-stock') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.report.all-product') }}"
                                            title="{{ translate('product_Report') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('product_Report') }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/products/' . Product::STOCK_REPORT[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.products.stock-report') }}"
                                            title="{{ __('product_stock') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ __('product_stock') }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/products/' . Product::REQUEST_RESTOCK_LIST[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.products.request-restock-list') }}"
                                            title="{{ translate('Request_Restock_List') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('Request_Restock_List') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @endif

                            @if (Helpers::module_permission_check('report') || Helpers::module_permission_check('branch_management'))
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/reports/unified') || Request::is('admin/branch/sales*') || Request::is('admin/stock/transfer-report*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="Branchs">
                                        <i class="tio-chart-bar-1 nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            Branchs
                                        </span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display: {{ Request::is('admin/reports/unified') || Request::is('admin/branch/sales*') || Request::is('admin/stock/transfer-report*') ? 'block' : 'none' }}">
                                        <li class="nav-item {{ Request::is('admin/reports/unified') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.reports.unified') }}"
                                                title="{{ translate('reports_&_analysis') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">
                                                    {{ translate('reports_&_analysis') }}
                                                </span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/branch/sales*') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.branch.sales-chart') }}"
                                                title="{{ translate('Branches_Charts') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">
                                                    {{ translate('Branches_Charts') }}
                                                </span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/stock/transfer-report*') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.stock.transfer-report') }}"
                                                title="{{ translate('stock transfers') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">
                                                    {{ translate('stock transfers') }}
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                            @if (Helpers::module_permission_check('report') || Helpers::module_permission_check('crm_section'))
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/crm/sales-report*') || Request::is('admin/crm/insights-report') || Request::is('admin/report/' . CrmDealSalesReport::VIEW[URI]) || Request::is('admin/report/' . CrmAgentSalesMatrixReport::VIEW[URI]) || Request::is('admin/report/' . CrmEmployeeChannelAssignmentReport::VIEW[URI]) || Request::is('admin/crm/chart-view') || Request::is('admin/ucm/insights-report') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="CRM">
                                        <i class="tio-chart-pie-1 nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            CRM
                                        </span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display: {{ Request::is('admin/crm/sales-report*') || Request::is('admin/crm/insights-report') || Request::is('admin/report/' . CrmDealSalesReport::VIEW[URI]) || Request::is('admin/report/' . CrmAgentSalesMatrixReport::VIEW[URI]) || Request::is('admin/report/' . CrmEmployeeChannelAssignmentReport::VIEW[URI]) || Request::is('admin/crm/chart-view') || Request::is('admin/ucm/insights-report') ? 'block' : 'none' }}">
                                        @if (Helpers::module_permission_check('report'))
                                            <li class="nav-item {{ Request::is('admin/crm/sales-report*') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.crm.sales-report') }}"
                                                    title="{{ translate('crm_sales_report') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">{{ translate('crm_sales_report') }}</span>
                                                </a>
                                            </li>
                                            <li class="nav-item {{ Request::is('admin/crm/insights-report') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.crm.insights-report') }}"
                                                    title="{{ __('CRM Insights Report') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">{{ __('CRM Insights Report') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class="nav-item {{ Request::is('admin/report/' . CrmDealSalesReport::VIEW[URI]) ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.report.crm-sales-performance') }}"
                                                    title="{{ translate('crm_sales_performance_report') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span
                                                        class="text-truncate">{{ translate('crm_sales_performance_report') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class="nav-item {{ Request::is('admin/report/' . CrmAgentSalesMatrixReport::VIEW[URI]) ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.report.crm-agent-sales-matrix') }}"
                                                    title="{{ translate('crm_agent_sales_matrix_report') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span
                                                        class="text-truncate">{{ translate('crm_agent_sales_matrix_report') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class="nav-item {{ Request::is('admin/report/' . CrmEmployeeChannelAssignmentReport::VIEW[URI]) ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.report.crm-employee-channel-assignment') }}"
                                                    title="{{ translate('crm_employee_channel_assignment_report') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span
                                                        class="text-truncate">{{ translate('crm_employee_channel_assignment_report') }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (Helpers::module_permission_check('crm_section'))
                                            <li class="nav-item {{ Request::is('admin/crm/chart-view') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.crm.chart.view') }}"
                                                    title="{{ translate('crm_charts') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">{{ translate('crm_charts') }}</span>
                                                </a>
                                            </li>
                                            <li class="nav-item {{ Request::is('admin/ucm/insights-report') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.ucm.insights-report') }}"
                                                    title="{{ __('VOIP Insights Report') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">{{ __('VOIP Insights Report') }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            @if (Helpers::module_permission_check('warranty_section'))
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/warranty/report*') || Request::is('admin/warranty/claim-chart') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="Warranty">
                                        <i class="tio-chart-bar-2 nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            Warranty
                                        </span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display: {{ Request::is('admin/warranty/report*') || Request::is('admin/warranty/claim-chart') ? 'block' : 'none' }}">
                                        <li class="nav-item {{ Request::is('admin/warranty/report/claims') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.warranty.report.claims') }}"
                                                title="{{ translate('claims_report') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ translate('claims_report') }}</span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/warranty/report/sla') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.warranty.report.sla') }}"
                                                title="{{ translate('sla_compliance') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ translate('sla_compliance') }}</span>
                                            </a>
                                        </li>
                                        <li
                                            class="nav-item {{ Request::is('admin/warranty/report/activations') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.warranty.report.activations') }}"
                                                title="{{ translate('activation_report') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ translate('activation_report') }}</span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/warranty/report/analytics') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.warranty.report.analytics') }}"
                                                title="{{ __('Warranty Analytics Report') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ __('Warranty Analytics Report') }}</span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/warranty/claim-chart') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.warranty.claim.chart') }}"
                                                title="{{ translate('warranty_claims_chart') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ translate('warranty_claims_chart') }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                            @if (Helpers::module_permission_check('wholesaler_section'))
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/wholesale/dashboard/reports/*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="{{ translate('wholesale') }}">
                                        <i class="tio-chart-bar-4 nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('wholesale') }}
                                        </span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display: {{ Request::is('admin/wholesale/dashboard/reports/*') ? 'block' : 'none' }}">
                                        <li class="nav-item {{ Request::is('admin/wholesale/dashboard/reports/revenue') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.wholesale.dashboard.reports.revenue') }}"
                                                title="{{ translate('wholesale_revenue_report') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">
                                                    {{ translate('wholesale_revenue_report') }}
                                                </span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/wholesale/dashboard/reports/pipeline') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.wholesale.dashboard.reports.pipeline') }}"
                                                title="{{ translate('wholesale_pipeline_report') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">
                                                    {{ translate('wholesale_pipeline_report') }}
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                        @endif
                        @if (Helpers::module_permission_check('branch_management'))
                            <li class="nav-item {{ Request::is('admin/branch*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle">{{ translate('Branch Management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/branch*') || Request::is('admin/branch/withdraw-method/*') || (Request::is('admin/orders/details/*') && request()->has('vendor-order-list')) ? 'active' : '' }} ">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('branch') }}">
                                    <i class="tio-users-switch nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('branch') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/branch*') || Request::is('admin/orders/details/*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/branch/' . Branch::ADD[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('add_New_Branch') }}"
                                            href="{{ route('admin.branch.add') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('add_New_Branch') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/branch/' . Branch::LIST[URI]) || Request::is('admin/branch/' . Branch::VIEW[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('vendor_List') }}"
                                            href="{{ route('admin.branch.branch-list') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('branch_List') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\Branch::count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item  {{ Request::is('admin/branch/' . Branch::BRANCH_STOCK_LIST[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.branch.branch-stock-list') }}"
                                            title="{{ translate('Branches_Stock_List') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('Branches_Stock_List') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- Product Inventory Dropdown
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/branch/product-inventory*') || Request::is('admin/branch/product-sells*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                            <i class="tio-update nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Product Inventory') }}
                            </span>
                        </a>

                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/branch/product-inventory*') || Request::is('admin/branch/product-sells*') ? 'block' : 'none' }}">

                            <li
                                class="nav-item {{ Request::is('admin/branch/product-inventory') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.branch.product-inventory') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Product Inventory') }}</span>
                                </a>
                            </li>

                            <li class="nav-item {{ Request::is('admin/branch/product-sells') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.branch.product-sells') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Product Sells Track') }}</span>
                                </a>
                            </li>
                        </ul>
                        </li> --}}


                            {{-- Stock Movement Dropdown --}}
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/branch/received*') || Request::is('admin/stock-request/' . StockRequest::ADD[URI]) || Request::is('admin/stock-request/' . StockRequest::LIST[URI]) || Request::is('admin/stock-transfer/' . StockTransfer::LIST[URI]) || Request::is('admin/stock-transfer/' . StockTransfer::ADD[URI]) || Request::is('admin/branch/request*') || Request::is('admin/branch/approve*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:">
                                    <i class="tio-swap-horizontal nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Branch_Stock') }}</span>
                                </a>

                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/branch/received*') || Request::is('admin/branch/request*') || Request::is('admin/stock-request/' . StockRequest::ADD[URI]) || Request::is('admin/stock-request/' . StockRequest::LIST[URI]) || Request::is('admin/stock-transfer/' . StockTransfer::LIST[URI]) || Request::is('admin/stock-transfer/' . StockTransfer::ADD[URI]) || Request::is('admin/branch/approve*') ? 'block' : 'none' }}">

                                    <li
                                        class="nav-item {{ Request::is('admin/stock-request/' . StockRequest::LIST[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.stock-request.list') }}"
                                            title="{{ translate('Stock_Request_List') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('Stock_Request_List') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/stock-request/' . StockRequest::ADD[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.stock-request.add') }}"
                                            title="{{ translate('add-stock-request') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{ translate('Add_New_Stock_Request') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/stock-transfer/' . StockTransfer::LIST[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.stock-transfer.list') }}"
                                            title="{{ translate('Stock_Transfert_List') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('Stock_Transfer_List') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/stock-transfer/' . StockTransfer::ADD[URI]) || Request::is('admin/stock-transfer/' . StockTransfer::ADD[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.stock-transfer.add') }}"
                                            title="{{ translate('Transfer_New_Stock') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('Transfer_New_Stock') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            {{-- <li class="{{ Request::is('admin/branch/stock-updates') ? 'active' : '' }}">
                        <a class="nav-link d-flex align-items-center gap-2"
                            href="{{ route('admin.branch.stock-updates') }}">
                            <i class="tio-update"></i>
                            <span>{{ translate('Product Stock Updates') }}</span>
                        </a>
                        </li> --}}

                            <!--{{-- Alerts and Thresholds Dropdown --}}-->
                            <!--<li class="navbar-vertical-aside-has-menu {{ Request::is('admin/branch/alerts*') ? 'active' : '' }}">-->
                            <!--    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">-->
                            <!--        <i class="tio-warning nav-icon"></i>-->
                            <!--        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Alerts and Thresholds') }}</span>-->
                            <!--    </a>-->

                            <!--    <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('admin/branch/alerts*') ? 'block' : 'none' }}">-->
                            <!--        <li class="nav-item {{ Request::is('admin/branch/alerts') ? 'active' : '' }}">-->
                            <!--            <a class="nav-link" href="{{ route('admin.branch.alerts') }}">-->
                            <!--                <span class="tio-circle nav-indicator-icon"></span>-->
                            <!--                <span class="text-truncate">{{ translate('Stock Alerts') }}</span>-->
                            <!--            </a>-->
                            <!--        </li>-->
                            <!--    </ul>-->
                            <!--</li>-->
                        @endif


                        @if (Helpers::module_permission_check('user_section'))
                            <li
                                class="nav-item {{ Request::is('admin/customer/' . Customer::LIST[URI]) || Request::is('admin/customer/' . Customer::VIEW[URI] . '*') || Request::is('admin/customer/' . Customer::SUBSCRIBER_LIST[URI]) || Request::is('admin/vendors/' . Vendor::ADD[URI]) || Request::is('admin/vendors/' . Vendor::LIST[URI]) || Request::is('admin/branch/' . Vendor::ADD[URI]) || Request::is('admin/branch/' . Vendor::LIST[URI]) || Request::is('admin/delivery-man*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle"
                                    title="">{{ translate('user_management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/wallet*') || Request::is('admin/customer/' . Customer::LIST[URI]) || Request::is('admin/customer/' . Customer::VIEW[URI] . '*') || Request::is('admin/reviews*') || Request::is('admin/customer/loyalty/' . Customer::LOYALTY_REPORT[URI]) ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('customers') }}">
                                    <i class="tio-wallet nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('customers') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/customer/wallet*') || Request::is('admin/customer/' . Customer::LIST[URI]) || Request::is('admin/customer/' . Customer::VIEW[URI] . '*') || Request::is('admin/reviews*') || Request::is('admin/customer/loyalty/' . Customer::LOYALTY_REPORT[URI]) ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/customer/' . Customer::LIST[URI]) || Request::is('admin/customer/' . Customer::VIEW[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.customer.list') }}"
                                            title="{{ translate('Customer_List') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('customer_List') }} </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/reviews*') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.reviews.list') }}"
                                            title="{{ translate('customer_Reviews') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('customer_Reviews') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/customer/wallet/' . CustomerWallet::REPORT[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('wallet') }}"
                                            href="{{ route('admin.customer.wallet.report') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('wallet') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/customer/wallet/' . CustomerWallet::BONUS_SETUP[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('wallet_Bonus_Setup') }}"
                                            href="{{ route('admin.customer.wallet.bonus-setup') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('wallet_Bonus_Setup') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/customer/loyalty/' . Customer::LOYALTY_REPORT[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('loyalty_Points') }}"
                                            href="{{ route('admin.customer.loyalty.report') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('loyalty_Points') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/vendors*') || Request::is('admin/vendors/withdraw-method/*') || (Request::is('admin/orders/details/*') && request()->has('vendor-order-list')) ? 'active' : '' }} d-none">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('vendors') }}">
                                    <i class="tio-users-switch nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('vendors') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/vendors*') || (Request::is('admin/orders/details/*') && request()->has('vendor-order-list')) ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/vendors/' . Vendor::ADD[URI]) ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('add_New_Vendor') }}"
                                            href="{{ route('admin.vendors.add') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('add_New_Vendor') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/vendors/' . Vendor::LIST[URI]) || Request::is('admin/vendors/' . Vendor::VIEW[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('vendor_List') }}"
                                            href="{{ route('admin.vendors.vendor-list') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('vendor_List') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/vendors/' . Vendor::WITHDRAW_LIST[URI]) || Request::is('admin/vendors/' . Vendor::WITHDRAW_VIEW[URI] . '/*') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.vendors.withdraw_list') }}"
                                            title="{{ translate('withdraws') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('withdraws') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/vendors/withdraw-method/*') ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.vendors.withdraw-method.list') }}"
                                            title="{{ translate('withdrawal_Methods') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('withdrawal_Methods') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle text-capitalize"
                                    href="javascript:" title="{{ translate('delivery_men') }}">
                                    <i class="tio-user nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                        {{ translate('delivery_men') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/delivery-man*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/delivery-man/' . DeliveryMan::ADD[URI]) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.delivery-man.add') }}"
                                            title="{{ translate('add_new') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('add_new') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/delivery-man/' . DeliveryMan::LIST[URI]) || Request::is('admin/delivery-man/' . DeliveryMan::UPDATE[URI] . '*') || Request::is('admin/delivery-man/' . DeliveryMan::EARNING_STATEMENT_OVERVIEW[URI] . '*') || Request::is('admin/delivery-man/' . DeliveryMan::ORDER_HISTORY_LOG[URI] . '*') || Request::is('admin/delivery-man/' . DeliveryMan::EARNING_OVERVIEW[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.delivery-man.list') }}"
                                            title="{{ translate('list') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('list') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/delivery-man/' . DeliverymanWithdraw::LIST[URI]) || Request::is('admin/delivery-man/' . DeliverymanWithdraw::VIEW[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.delivery-man.withdraw-list') }}"
                                            title="{{ translate('withdraws') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('withdraws') }}</span>
                                        </a>
                                    </li>

                                    <li
                                        class="nav-item {{ Request::is('admin/delivery-man/emergency-contact') ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.delivery-man.emergency-contact.index') }}"
                                            title="{{ translate('emergency_contact') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('Emergency_Contact') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            @if (auth('admin')->user()->can('rbac.roles.manage') || auth('admin')->user()->can('employee_management.read'))
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/employee*') || Request::is('admin/custom-role*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="{{ translate('employees') }}">
                                        <i class="tio-user nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('employees') }}
                                        </span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display: {{ Request::is('admin/employee*') || Request::is('admin/custom-role*') ? 'block' : 'none' }}">
                                        @if (auth('admin')->user()->can('rbac.roles.manage'))
                                            <li
                                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/custom-role/' . Employee::ADD[URI]) ? 'active' : '' }}">
                                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                                    href="{{ route('admin.custom-role.create') }}"
                                                    title="{{ translate('employee_Role_Create') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span
                                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                        {{ translate('employee_Role_Create') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/custom-role/' . Employee::VIEW[URI]) ? 'active' : '' }}">
                                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                                    href="{{ route('admin.custom-role.view-all') }}"
                                                    title="{{ translate('employee_Roles') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span
                                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                        {{ translate('employee_Roles') }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth('admin')->user()->can('employee_management.read'))
                                            <li
                                                class="nav-item {{ Request::is('admin/employee/' . Employee::LIST[URI]) || Request::is('admin/employee/' . Employee::ADD[URI]) || Request::is('admin/employee/' . Employee::UPDATE[URI] . '*') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.employee.list') }}"
                                                    title="{{ translate('employees') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">{{ translate('employees') }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/department*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('department') }}">
                                    <i class="tio-users-switch nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('department') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/department*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/department/' . Department::ADD[URI]) || Request::is('admin/department/' . Department::USER_ADD[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('add_New_Department') }}"
                                            href="{{ route('admin.department.add') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('add_New_Department') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/department/' . Department::LIST[URI]) || Request::is('admin/department/' . Department::USER_VIEW[URI] . '*') ? 'active' : '' }}">
                                        <a class="nav-link" title="{{ translate('department_List') }}"
                                            href="{{ route('admin.department.list') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('Department_List') }}
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/' . Customer::SUBSCRIBER_LIST[URI]) ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.customer.subscriber-list') }}"
                                    title="{{ translate('subscribers') }}">
                                    <span class="tio-user nav-icon"></span>
                                    <span class="text-truncate">{{ translate('subscribers') }} </span>
                                </a>
                            </li>
                        @endif

                        @if (Helpers::module_permission_check('crm_section'))
                            <li class="nav-item {{ Request::is('admin/department/*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle" title="">{{ translate('crm_management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/crm/dashboard*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    title="{{ translate('dashboard') }}"
                                    href="{{ route('admin.crm.dashboard') }}">
                                    <i class="tio-home-vs-1-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('dashboard') }}
                                    </span>
                                </a>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/crm/index') ? 'active' : '' }}">
                                <a class="nav-link" title="{{ translate('inbox') }}"
                                    href="{{ route('admin.crm.index') }}">
                                    <i class="tio-chat nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('inbox') }}
                                    </span>
                                </a>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/crm/lead*') ? 'active' : '' }}">
                                <a class="nav-link" title="{{ translate('leads') }}"
                                    href="{{ route('admin.crm.lead.index') }}">
                                    <i class="tio-chat nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('leads') }}
                                    </span>
                                </a>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/crm/deals/*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="#"
                                    title="{{ translate('deals') }}">
                                    <i class="tio-wallet nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('deals') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/crm/deals/wholesale/*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/crm/deals/wholesale/*') ? 'active' : '' }}">
                                        <a class=" nav-link" href="{{ route('admin.crm.deals.wholesale.index') }}"
                                            title="{{ translate('wholesaler_Deals') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('wholesaler_Deals') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/product/' . WholeSalesProducts::LIST[URI]) || Request::is('admin/wholesale/product/' . WholeSalesProducts::PRODUCT_VIEW[URI] . '/*') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.crm.deals.retail.list') }}"
                                            title="{{ translate('retail_Deals') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('retail_Deals') }}

                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/support-ticket' . SupportTicket::LIST[URI]) || Request::is('admin/support-ticket/career') || Request::is('admin/support-ticket/view/*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('Tickets') }}">
                                    <i class="tio-support nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('Tickets') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/support-ticket/view/*') ? 'block' : 'none' }}">
                                    <!-- <li
                                    class="nav-item {{ Request::is('admin/orders/' . Order::LIST[URI] . '/all') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.support-ticket.view', ['all']) }}"
                                        title="{{ translate('all') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ translate('all') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Models\SupportTicket::count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li> -->
                                    <li
                                        class="nav-item {{ Request::is('admin/support-ticket/view/' . SupportTicket::SUPPORT[URI]) ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.support-ticket.view', ['support']) }}"
                                            title="{{ translate('support') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('support') }}
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{ \App\Models\SupportTicket::where(['type' => 'support', 'status' => 1])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/support-ticket/view/' . SupportTicket::COMPLAINT[URI]) ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.support-ticket.view', ['complaint']) }}"
                                            title="{{ translate('complaint') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('complaint') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\SupportTicket::where(['type' => 'complaint', 'status' => 36])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/support-ticket/career') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.support-ticket.career.index') }}"
                                            title="{{ translate('career') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('career') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\SupportTicket::where(['type' => 'career', 'status' => 27])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/support-ticket/view/' . SupportTicket::SERVICE[URI]) ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.support-ticket.view', ['service']) }}"
                                            title="{{ translate('service') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('service') }}
                                                <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{ \App\Models\SupportTicket::where(['type' => 'service', 'status' => 20])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/support-ticket/view/' . SupportTicket::RETAIL[URI]) ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.support-ticket.view', ['retail']) }}"
                                            title="{{ translate('retail') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('retail') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\SupportTicket::where(['type' => 'retail', 'status' => 43])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/support-ticket/view/' . SupportTicket::WHOLESALE[URI]) ? 'active' : '' }}">
                                        <a class="nav-link "
                                            href="{{ route('admin.support-ticket.view', ['wholesale']) }}"
                                            title="{{ translate('wholesale') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('wholesale') }}
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{ \App\Models\SupportTicket::where(['type' => 'wholesale', 'status' => 56])->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/messages*') ? 'active' : '' }}">
                                <a class="nav-link" title="{{ translate('chat_Box') }}"
                                    href="{{ route('admin.messages.index', ['type' => 'customer']) }}">
                                    <i class="tio-chat nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('chat_Box') }}
                                    </span>
                                </a>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/crm/calendar*') ? 'active' : '' }}">
                                <a class="nav-link" title="{{ translate('calendar') }}"
                                    href="{{ route('admin.crm.calendar.index') }}">
                                    <i class="tio-chat nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('calendar') }}
                                    </span>
                                </a>
                            </li>
                            @if (Helpers::module_permission_check('crm_section', 'sla_list'))
                                <li
                                    class="navbar-vertical-aside-has-menu  {{ Request::is('admin/sla*') ? 'active' : '' }}">
                                    <a class="nav-link" title="{{ translate('sla_configration') }}"
                                        href="{{ route('admin.sla.index') }}">
                                        <i class="tio-chat nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('sla_configration') }}
                                        </span>
                                    </a>
                                </li>
                            @endif

                        @endif

                        <li
                            class="navbar-vertical-aside-has-menu {{ auth('admin')->id() == 1 ? 'd-none' : '' }} {{ Request::is('admin/notifications/list') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                title="{{ translate('notifications') }}"
                                href="{{ route('admin.notifications.list') }}">
                                <span class="nav-icon">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_5926_1152)">
                                            <path
                                                d="M10 20.5C11.1046 20.5 12 19.6046 12 18.5H8C8 19.6046 8.89543 20.5 10 20.5ZM16 14.5V9.5C16 6.57436 14.3682 4.15379 11.75 3.53235V3C11.75 2.30964 11.1904 1.75 10.5 1.75C9.80964 1.75 9.25 2.30964 9.25 3V3.53235C6.63184 4.15379 5 6.57436 5 9.5V14.5L3 16.5V17.5H17V16.5L16 14.5Z"
                                                fill="#fff"></path>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_5926_1152">
                                                <rect width="20" height="20" fill="white"
                                                    transform="translate(0 0.5)">
                                                </rect>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </span>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('notifications') }}
                                </span>
                            </a>
                        </li>
                        @if (Helpers::module_permission_check('wholesaler_section'))
                            <li class="nav-item {{ Request::is('admin/wholesale/*') ? 'scroll-here' : '' }}">
                                <small class="nav-subtitle"
                                    title="">{{ translate('Wholesaler_Management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/wholesale/dashboard' . WholeSaler::DASHBOARD[URI]) ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                    title="{{ translate('dashboard') }}"
                                    href="{{ route('admin.wholesale.dashboard.index') }}">
                                    <i class="tio-home-vs-1-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('dashboard') }}
                                    </span>
                                </a>
                            </li>
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/wholesale/business/*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="#" title="{{ translate('Wholesaler_Business') }}">
                                    <i class="tio-user nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Wholesaler_Business') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/wholesale/business/*') ? 'block' : 'none' }}">

                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/business/tier') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.wholesale.business.wholesaler.tier.view') }}"
                                            title="{{ translate('Wholesaler Tier') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Tiers') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WholesaleTier::count() }}
                                                </span>
                                            </span>

                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/business/request') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.wholesale.business.request') }}"
                                            title="{{ translate('Wholesaler Requests') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Join Requests') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WholeSalerBusiness::whereHas('wholesaler', function ($query) {
                                                        $query->where('user_type', 1)->where('wholesaler_status', '!=', 1);
                                                    })->count() }}
                                                </span>

                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/business/list') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.wholesale.business.list') }}"
                                            title="{{ translate('Wholesalers') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Wholesalers') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WholeSalerBusiness::whereHas('wholesaler', function ($query) {
                                                        $query->where('user_type', 1)->where('wholesaler_status', '!=', 0);
                                                    })->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/business/purchase-request') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.wholesale.business.order.request') }}"
                                            title="{{ translate('Order Requests') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Purchase Requests') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WholesalePurchaseOrder::count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/business/quotation-sent') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.wholesale.business.wholesale.order') }}"
                                            title="{{ translate('wholesale_Orders') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Quotation_Sent') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WholesaleQuotation::count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/business/create-quotation') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.wholesale.business.create-quotation') }}"
                                            title="{{ translate('wholesale_Orders') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Create_Quotation') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/business/order') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.wholesale.business.wholesale.confirmedorder') }}"
                                            title="{{ translate('wholesale_Orders') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Confirmed_Orders') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WholesaleConfirmOrder::count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/wholesale/product/*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="#" title="{{ translate('Whole_Sellers') }}">
                                    <i class="tio-wallet nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Wholesale_Products') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/wholesale/*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/product/' . WholeSalesProducts::ADD[URI]) || Request::is('admin/wholesale/product/' . WholeSalesProducts::UPDATE_VIEW[URI] . '/*') ? 'active' : '' }}">
                                        <a class=" nav-link" href="{{ route('admin.wholesale.product.add') }}"
                                            title="{{ translate('add_New_Wholesaler') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('add_New_Product') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/wholesale/product/' . WholeSalesProducts::LIST[URI]) || Request::is('admin/wholesale/product/' . WholeSalesProducts::PRODUCT_VIEW[URI] . '/*') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.wholesale.product.list') }}"
                                            title="{{ translate('Whole_seller_list') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Product_List') }}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{ \App\Models\WholeSaleProducts::where('status', 1)
                                                        ->whereHas('product', function ($query) {
                                                            $query->whereNull('deleted_at')
                                                                ->where('status', 1)
                                                                ->where('request_status', 1)
                                                                ->where(function ($availabilityQuery) {
                                                                    $availabilityQuery->whereIn('product_type', ['digital', 'services'])
                                                                        ->orWhere('current_stock', '>', 0);
                                                                });
                                                        })->count() }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (Helpers::module_permission_check('system_settings'))
                            <li
                                class="nav-item {{ Request::is('admin/business-settings/web-config') ||
                                Request::is('admin/product-settings') ||
                                Request::is('admin/business-settings/' . SocialMedia::VIEW[URI]) ||
                                Request::is('admin/business-settings/web-config/' . BusinessSettings::APP_SETTINGS[URI]) ||
                                Request::is('admin/business-settings/' . Pages::TERMS_CONDITION[URI]) ||
                                Request::is('admin/business-settings/' . Pages::VIEW[URI] . '*') ||
                                Request::is('admin/business-settings/' . Pages::PRIVACY_POLICY[URI]) ||
                                Request::is('admin/business-settings/' . Pages::ABOUT_US[URI]) ||
                                Request::is('admin/helpTopic/' . HelpTopic::LIST[URI]) ||
                                Request::is('admin/business-settings/' . PushNotification::INDEX[URI]) ||
                                Request::is('admin/business-settings/' . Mail::VIEW[URI]) ||
                                Request::is('admin/business-settings/web-config/' . DatabaseSetting::VIEW[URI]) ||
                                Request::is('admin/business-settings/web-config/' . EnvironmentSettings::VIEW[URI]) ||
                                Request::is('admin/business-settings/' . BusinessSettings::INDEX[URI]) ||
                                Request::is('admin/system-setup/login-settings/' . SystemSetup::CUSTOMER_LOGIN_SETUP[URI]) ||
                                Request::is('admin/system-setup/login-settings/' . SystemSetup::OTP_SETUP[URI]) ||
                                Request::is('admin/system-setup/login-settings/' . SystemSetup::LOGIN_URL_SETUP[URI]) ||
                                Request::is('admin/system-settings/' . SoftwareUpdate::VIEW[URI]) ||
                                Request::is('admin/business-settings/web-config/theme/' . ThemeSetup::VIEW[URI]) ||
                                Request::is('admin/business-settings/shipping-method/' . ShippingMethod::UPDATE[URI] . '*') ||
                                Request::is('admin/business-settings/shipping-method/' . ShippingMethod::INDEX[URI]) ||
                                Request::is('admin/business-settings/delivery-restriction') ||
                                Request::is('admin/business-settings/invoice-settings') ||
                                Request::is('admin/seo-settings/' . SEOSettings::WEB_MASTER_TOOL[URI]) ||
                                Request::is('admin/seo-settings/' . SEOSettings::ROBOT_TXT[URI]) ||
                                Request::is('admin/seo-settings/' . SiteMap::SITEMAP[URI]) ||
                                Request::is('admin/seo-settings/robots-meta-content*') ||
                                Request::is('admin/error-logs/' . ErrorLogs::INDEX[URI]) ||
                                Request::is('admin/addon')
                                    ? 'scroll-here'
                                    : '' }}">

                                <small class="nav-subtitle"
                                    title="">{{ translate('system_Settings') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('business_Setup') }}">
                                    <i class="tio-pages-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('business_Setup') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/business-settings/web-config') ||
                                    Request::is('admin/product-settings') ||
                                    Request::is('admin/product-settings/' . InhouseShop::VIEW[URI]) ||
                                    Request::is('admin/business-settings/payment-method/' . PaymentMethod::PAYMENT_OPTION[URI]) ||
                                    Request::is('admin/business-settings/vendor-settings') ||
                                    Request::is('admin/customer/' . Customer::SETTINGS[URI]) ||
                                    Request::is('admin/business-settings/delivery-man-settings') ||
                                    Request::is('admin/business-settings/shipping-method/' . ShippingMethod::UPDATE[URI] . '*') ||
                                    Request::is('admin/business-settings/shipping-method/' . ShippingMethod::INDEX[URI]) ||
                                    Request::is('admin/business-settings/order-settings/index') ||
                                    Request::is('admin/' . BusinessSettings::PRODUCT_SETTINGS[URI]) ||
                                    Request::is('admin/business-settings/invoice-settings') ||
                                    Request::is('admin/business-settings/priority-setup') ||
                                    Request::is('admin/seo-settings/' . SEOSettings::WEB_MASTER_TOOL[URI]) ||
                                    Request::is('admin/seo-settings/' . SEOSettings::ROBOT_TXT[URI]) ||
                                    Request::is('admin/seo-settings/' . SiteMap::SITEMAP[URI]) ||
                                    Request::is('admin/seo-settings/robots-meta-content*') ||
                                    Request::is('admin/error-logs/' . ErrorLogs::INDEX[URI]) ||
                                    Request::is('admin/business-settings/delivery-restriction')
                                        ? 'block'
                                        : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/business-settings/web-config') ||
                                        Request::is('admin/product-settings') ||
                                        Request::is('admin/business-settings/payment-method/' . PaymentMethod::PAYMENT_OPTION[URI]) ||
                                        Request::is('admin/business-settings/vendor-settings') ||
                                        Request::is('admin/customer/' . Customer::SETTINGS[URI]) ||
                                        Request::is('admin/business-settings/delivery-man-settings') ||
                                        Request::is('admin/business-settings/shipping-method/' . ShippingMethod::UPDATE[URI] . '*') ||
                                        Request::is('admin/business-settings/shipping-method/' . ShippingMethod::INDEX[URI]) ||
                                        Request::is('admin/business-settings/order-settings/index') ||
                                        Request::is('admin/' . BusinessSettings::PRODUCT_SETTINGS[URI]) ||
                                        Request::is('admin/business-settings/invoice-settings') ||
                                        Request::is('admin/business-settings/priority-setup') ||
                                        Request::is('admin/business-settings/delivery-restriction')
                                            ? 'active'
                                            : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.business-settings.web-config.index') }}"
                                            title="{{ translate('business_Settings') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('business_Settings') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/product-settings/' . InhouseShop::VIEW[URI]) ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.product-settings.inhouse-shop') }}"
                                            title="{{ translate('in-house_Shop') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('in-house_Shop') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/seo-settings/' . SEOSettings::WEB_MASTER_TOOL[URI]) ||
                                        Request::is('admin/seo-settings/' . SEOSettings::ROBOT_TXT[URI]) ||
                                        Request::is('admin/seo-settings/' . SiteMap::SITEMAP[URI]) ||
                                        Request::is('admin/seo-settings/robots-meta-content*') ||
                                        Request::is('admin/error-logs/' . ErrorLogs::INDEX[URI])
                                            ? 'active'
                                            : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.seo-settings.web-master-tool') }}"
                                            title="{{ translate('SEO_Settings') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('SEO_Settings') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="navbar-vertical-aside-has-menu ">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('system_Setup') }}">
                                    <i class="tio-pages-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('system_Setup') }}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/business-settings/web-config/' . EnvironmentSettings::VIEW[URI]) ||
                                    Request::is('admin/business-settings/web-config/' . SiteMap::SITEMAP[URI]) ||
                                    Request::is('admin/currency/' . Currency::LIST[URI]) ||
                                    Request::is('admin/currency/' . Currency::UPDATE[URI] . '*') ||
                                    Request::is('admin/business-settings/web-config/' . DatabaseSetting::VIEW[URI]) ||
                                    Request::is('admin/business-settings/language*') ||
                                    Request::is('admin/business-settings/web-config/theme/' . ThemeSetup::VIEW[URI]) ||
                                    Request::is('admin/system-settings/' . SoftwareUpdate::VIEW[URI]) ||
                                    Request::is('admin/system-setup/login-settings/' . SystemSetup::OTP_SETUP[URI]) ||
                                    Request::is('admin/system-setup/login-settings/' . SystemSetup::CUSTOMER_LOGIN_SETUP[URI]) ||
                                    Request::is('admin/system-setup/login-settings/' . SystemSetup::LOGIN_URL_SETUP[URI]) ||
                                    Request::is('admin/business-settings/web-config/' . BusinessSettings::APP_SETTINGS[URI]) ||
                                    Request::is('admin/business-settings/email-templates/*') ||
                                    Request::is('admin/addon')
                                        ? 'block'
                                        : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/business-settings/web-config/' . EnvironmentSettings::VIEW[URI]) ||
                                        Request::is('admin/business-settings/web-config/' . SiteMap::SITEMAP[URI]) ||
                                        Request::is('admin/currency/' . Currency::LIST[URI]) ||
                                        Request::is('admin/currency/' . Currency::UPDATE[URI] . '*') ||
                                        Request::is('admin/business-settings/web-config/' . DatabaseSetting::VIEW[URI]) ||
                                        Request::is('admin/business-settings/language*') ||
                                        Request::is('admin/system-settings/' . SoftwareUpdate::VIEW[URI]) ||
                                        Request::is('admin/business-settings/web-config/' . BusinessSettings::APP_SETTINGS[URI]) ||
                                        Request::is('admin/business-settings/invoice-settings/' . InvoiceSettings::VIEW[URI]) ||
                                        Request::is('admin/business-settings/delivery-restriction')
                                            ? 'active'
                                            : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.business-settings.web-config.environment-setup') }}"
                                            title="{{ translate('system_Settings') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('system_Settings') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="nav-item {{ Request::is('admin/system-setup/login-settings/' . SystemSetup::LOGIN_URL_SETUP[URI]) ||
                                        Request::is('admin/system-setup/login-settings/' . SystemSetup::CUSTOMER_LOGIN_SETUP[URI]) ||
                                        Request::is('admin/system-setup/login-settings/' . SystemSetup::OTP_SETUP[URI])
                                            ? 'active'
                                            : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.system-setup.login-settings.customer-login-setup') }}"
                                            title="{{ translate('login_Settings') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('login_Settings') }}
                                            </span>
                                        </a>
                                    </li>

                                    <li
                                        class="nav-item {{ Request::is('admin/business-settings/email-templates/*') ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.business-settings.email-templates.view', ['admin', EmailTemplateKey::ADMIN_EMAIL_LIST[0]]) }}"
                                            title="{{ translate('in-house_Shop') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">
                                                {{ translate('email_template') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="navbar-vertical-aside-has-menu">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('3rd_Party') }}">
                                    <span class="tio-key nav-icon"></span>
                                    <span class="text-truncate">{{ translate('3rd_Party') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/business-settings/mail' . Mail::VIEW[URI]) ||
                                    Request::is('admin/business-settings/offline-payment-method/' . OfflinePaymentMethod::INDEX[URI]) ||
                                    Request::is('admin/business-settings/offline-payment-method/' . OfflinePaymentMethod::ADD[URI]) ||
                                    Request::is('admin/business-settings/offline-payment-method/' . OfflinePaymentMethod::UPDATE[URI] . '/*') ||
                                    Request::is('admin/business-settings/' . SMSModule::VIEW[URI]) ||
                                    Request::is('admin/business-settings/' . Recaptcha::VIEW[URI]) ||
                                    Request::is('admin/social-login/' . SocialLoginSettings::VIEW[URI]) ||
                                    Request::is('admin/social-media-chat/' . SocialMediaChat::VIEW[URI]) ||
                                    Request::is('admin/business-settings/' . GoogleMapAPI::VIEW[URI]) ||
                                    Request::is('admin/business-settings/payment-method') ||
                                    Request::is('admin/business-settings/' . BusinessSettings::ANALYTICS_INDEX[URI]) ||
                                    Request::is('admin/storage-connection-settings/' . StorageConnectionSettings::INDEX[URI]) ||
                                    Request::is('admin/firebase-otp-verification/' . FirebaseOTPVerification::INDEX[URI]) ||
                                    Request::is('admin/business-settings/payment-method/offline-payment*')
                                        ? 'block'
                                        : 'none' }}">
                                    <li
                                        class="nav-item {{ Request::is('admin/business-settings/payment-method') ||
                                        Request::is('admin/business-settings/payment-method/offline-payment*') ||
                                        Request::is('admin/business-settings/offline-payment-method/' . OfflinePaymentMethod::INDEX[URI]) ||
                                        Request::is('admin/business-settings/offline-payment-method/' . OfflinePaymentMethod::ADD[URI]) ||
                                        Request::is('admin/business-settings/offline-payment-method/' . OfflinePaymentMethod::UPDATE[URI] . '/*')
                                            ? 'active'
                                            : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.business-settings.payment-method.index') }}"
                                            title="{{ translate('payment_methods') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ translate('payment_methods') }}
                                            </span>
                                        </a>
                                    </li>

                                    <li
                                        class="navbar-vertical-aside-has-menu
                                    {{ Request::is('admin/business-settings/' . BusinessSettings::ANALYTICS_INDEX[URI]) ? 'active' : '' }}
                                    ">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.business-settings.analytics-index') }}"
                                            title="{{ translate('Marketing_Tool') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Marketing_Tool') }}
                                            </span>
                                        </a>
                                    </li>

                                    <li
                                        class="navbar-vertical-aside-has-menu
                                    {{ Request::is('admin/business-settings/mail' . Mail::VIEW[URI]) ||
                                    Request::is('admin/business-settings/' . SMSModule::VIEW[URI]) ||
                                    Request::is('admin/business-settings/' . Recaptcha::VIEW[URI]) ||
                                    Request::is('admin/social-login/' . SocialLoginSettings::VIEW[URI]) ||
                                    Request::is('admin/social-media-chat/' . SocialMediaChat::VIEW[URI]) ||
                                    Request::is('admin/storage-connection-settings/' . StorageConnectionSettings::INDEX[URI]) ||
                                    Request::is('admin/firebase-otp-verification/' . FirebaseOTPVerification::INDEX[URI]) ||
                                    Request::is('admin/business-settings/' . GoogleMapAPI::VIEW[URI])
                                        ? 'active'
                                        : '' }}
                                    ">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.social-media-chat.view') }}"
                                            title="{{ translate('other_Configurations') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('other_Configurations') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="nav-item {{ Request::is('admin/business-settings/' . Pages::TERMS_CONDITION[URI]) ||
                                Request::is('admin/business-settings/' . Pages::VIEW[URI] . '*') ||
                                Request::is('admin/business-settings/' . Pages::PRIVACY_POLICY[URI]) ||
                                Request::is('admin/business-settings/' . Pages::ABOUT_US[URI]) ||
                                Request::is('admin/business-settings/' . Pages::COOKIE_SETTINGS[URI]) ||
                                Request::is('admin/helpTopic/' . HelpTopic::LIST[URI]) ||
                                Request::is('admin/business-settings/' . FeaturesSection::VIEW[URI]) ||
                                Request::is('admin/business-settings/vendor-registration-settings/*') ||
                                Request::is('admin/business-settings/' . FeaturesSection::COMPANY_RELIABILITY[URI]) ||
                                Request::is('admin/content-management*') ||
                                Request::is('admin/file-manager*') ||
                                Request::is('admin/business-settings/' . SocialMedia::VIEW[URI]) ||
                                Request::is('admin/blog*')
                                    ? 'scroll-here'
                                    : '' }}">
                                <small class="nav-subtitle" title="">{{ __('Content Management') }}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/' . Pages::TERMS_CONDITION[URI]) ||
                                Request::is('admin/business-settings/' . Pages::VIEW[URI] . '*') ||
                                Request::is('admin/business-settings/' . Pages::PRIVACY_POLICY[URI]) ||
                                Request::is('admin/business-settings/' . Pages::ABOUT_US[URI]) ||
                                Request::is('admin/business-settings/' . Pages::COOKIE_SETTINGS[URI]) ||
                                Request::is('admin/helpTopic/' . HelpTopic::LIST[URI]) ||
                                Request::is('admin/business-settings/' . FeaturesSection::VIEW[URI]) ||
                                Request::is('admin/business-settings/vendor-registration-settings/*') ||
                                Request::is('admin/business-settings/' . FeaturesSection::COMPANY_RELIABILITY[URI]) ||
                                Request::is('admin/content-management*')
                                    ? 'active'
                                    : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('Pages_&_Media') }}">
                                    <i class="tio-pages-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('Pages_&_Media') }}
                                    </span>
                                </a>

                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/business-settings/terms-condition') ||
                                    Request::is('admin/business-settings/page*') ||
                                    Request::is('admin/business-settings/privacy-policy') ||
                                    Request::is('admin/business-settings/cookie-settings') ||
                                    Request::is('admin/business-settings/about-us') ||
                                    Request::is('admin/helpTopic/list') ||
                                    Request::is('admin/business-settings/social-media') ||
                                    Request::is('admin/file-manager*') ||
                                    Request::is('admin/business-settings/features-section') ||
                                    Request::is('admin/business-settings/vendor-registration-settings/*') ||
                                    Request::is('admin/business-settings/wholesaler-registration-settings/*') ||
                                    Request::is('admin/content-management*')
                                        ? 'block'
                                        : 'none' }}">

                                    {{-- Existing 4 items --}}
                                    <li
                                        class="nav-item {{ Request::is('admin/business-settings/' . Pages::TERMS_CONDITION[URI]) ||
                                        Request::is('admin/business-settings/' . Pages::VIEW[URI] . '*') ||
                                        Request::is('admin/business-settings/' . Pages::PRIVACY_POLICY[URI]) ||
                                        Request::is('admin/business-settings/' . Pages::ABOUT_US[URI]) ||
                                        Request::is('admin/business-settings/' . Pages::COOKIE_SETTINGS[URI]) ||
                                        Request::is('admin/helpTopic/' . HelpTopic::LIST[URI]) ||
                                        Request::is('admin/business-settings/' . FeaturesSection::VIEW[URI]) ||
                                        Request::is('admin/business-settings/' . FeaturesSection::COMPANY_RELIABILITY[URI])
                                            ? 'active'
                                            : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('admin.business-settings.terms-condition') }}"
                                            title="{{ translate('business_Pages') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('business_Pages') }}</span>
                                        </a>
                                    </li>

                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/' . SocialMedia::VIEW[URI]) ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.business-settings.social-media') }}"
                                            title="{{ translate('social_Media_Links') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('social_Media_Links') }}</span>
                                        </a>
                                    </li>

                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/file-manager*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.file-manager.index') }}"
                                            title="{{ translate('gallery') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('gallery') }}</span>
                                        </a>
                                    </li>
                                    @if ($web_config['business_mode'] == 'multi' && $web_config['seller_registration'])
                                        <li
                                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/vendor-registration-settings/*') ? 'active' : '' }}">
                                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                                href="{{ route('admin.business-settings.vendor-registration-settings.index') }}"
                                                title="{{ translate('vendor_Registration') }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span
                                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('vendor_Registration') }}</span>
                                            </a>
                                        </li>
                                    @endif
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/wholesaler-registration-settings/*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.business-settings.wholesaler-registration-settings.index') }}"
                                            title="{{ translate('wholesaler_Registration') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('wholesaler_Registration') }}</span>
                                        </a>
                                    </li>

                                    {{-- New Added Pages --}}
                                    @foreach ($contentPages as $slug => $page)
                                        <li
                                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/content-management/' . $slug) ? 'active' : '' }}">
                                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                                href="{{ route('admin.content-management.' . $slug) }}"
                                                title="{{ translate($page['label']) }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span
                                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate($page['label']) }}</span>
                                            </a>
                                        </li>
                                    @endforeach

                                </ul>
                            </li>
                            @if (Helpers::module_permission_check('blog_management'))
                                @if (Route::has('admin.blog.view'))
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/blog*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                            href="javascript:" title="{{ translate('blog') }}">
                                            <i class="tio-file-text nav-icon"></i> <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('blog') }}</span>
                                        </a>
                                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                            style="display: {{ Request::is('admin/blog*') ? 'block' : 'none' }}">
                                            <li class="nav-item {{ Request::is('admin/blog/*') }}">
                                                <a class="nav-link" title="{{ translate('add_New') }}"
                                                    href="{{ route('admin.blog.add') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">
                                                        {{ translate('add_new') }}
                                                    </span>
                                                </a>
                                            </li>
                                            <li class="nav-item {{ Request::is('admin/blog/*') }}">
                                                <a class="nav-link" title="{{ translate('List') }}"
                                                    href="{{ route('admin.blog.view') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">
                                                        {{ translate('List') }}
                                                    </span>
                                                </a>
                                            </li>

                                        </ul>
                                    </li>
                                @endif
                            @endif

                            @if (count(config('addon_admin_routes')) > 0)
                                <li
                                    class="navbar-vertical-aside-has-menu
                                @foreach (config('addon_admin_routes') as $routes)
                                    @foreach ($routes as $route)
                                        {{ strstr(Request::url(), $route['path']) ? 'active' : '' }} @endforeach
                                @endforeach
                            ">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="{{ translate('Pages_&_Media') }}">
                                        <i class="tio-puzzle nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('addon_Menus') }}
                                        </span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display:
                                    @foreach (config('addon_admin_routes') as $routes)
                                        @foreach ($routes as $route)
                                            {{ strstr(Request::url(), $route['path']) ? 'block' : '' }} @endforeach
                                    @endforeach
                                    ">
                                        @foreach (config('addon_admin_routes') as $routes)
                                            @foreach ($routes as $route)
                                                <li
                                                    class="navbar-vertical-aside-has-menu {{ strstr(Request::url(), $route['path']) ? 'active' : '' }}">

                                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                                        href="{{ $route['url'] }}"
                                                        title="{{ translate($route['name']) }}">
                                                        <span class="tio-circle nav-indicator-icon"></span>
                                                        <span
                                                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                            {{ translate($route['name']) }}
                                                        </span>
                                                    </a>

                                                </li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endif
                        <li class="nav-item pt-5">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>
<script>
    $(function() {
        const branchSelectModal = $('#branchSelectModal');

        if (branchSelectModal.length) {
            branchSelectModal.appendTo('body');
            branchSelectModal.on('hidden.bs.modal', function() {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
        }

        if (!$('.modal.show').length) {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }
    });

    function handlePOSClick(branchCount) {
        if (branchCount > 1) {
            $('#branchSelectModal').modal('show');
        } else {
            let selectedBranchId = $('#posBranchId option:eq(1)').val(); // get first branch
            if (selectedBranchId) {
                window.location.href = '/admin/pos?branch_id=' + selectedBranchId;
            } else {
                alert(@json(__('No branch found.')));
            }
        }
    }

    $('#posBranchForm').on('submit', function(e) {
        e.preventDefault();
        let branchId = $('#posBranchId').val();
        if (branchId) {
            window.location.href = '/admin/pos?branch_id=' + branchId;
        } else {
            alert(@json(__('Please select a branch.')));
        }
    });
</script>

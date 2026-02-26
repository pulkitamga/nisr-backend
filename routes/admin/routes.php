<?php

use App\Enums\ViewPaths\Admin\AddonSetup;
use App\Enums\ViewPaths\Admin\AllPagesBanner;
use App\Enums\ViewPaths\Admin\Attribute;
use App\Enums\ViewPaths\Admin\Banner;
use App\Enums\ViewPaths\Admin\Branch;
use App\Enums\ViewPaths\Admin\Brand;
use App\Enums\ViewPaths\Admin\BusinessSettings;
use App\Enums\ViewPaths\Admin\Cart;
use App\Enums\ViewPaths\Admin\Category;
use App\Enums\ViewPaths\Admin\Chatting;
use App\Enums\ViewPaths\Admin\ClearanceSale;
use App\Enums\ViewPaths\Admin\Complaint;
use App\Enums\ViewPaths\Admin\Contact;
use App\Enums\ViewPaths\Admin\Coupon;
use App\Enums\ViewPaths\Admin\CrmAgentSalesMatrixReport;
use App\Enums\ViewPaths\Admin\CrmEmployeeChannelAssignmentReport;
use App\Enums\ViewPaths\Admin\Crm;
use App\Enums\ViewPaths\Admin\CrmDealSalesReport;
use App\Enums\ViewPaths\Admin\Currency;
use App\Enums\ViewPaths\Admin\Customer;
use App\Enums\ViewPaths\Admin\CustomerWallet;
use App\Enums\ViewPaths\Admin\CustomRole;
use App\Enums\ViewPaths\Admin\Dashboard;
use App\Enums\ViewPaths\Admin\DatabaseSetting;
use App\Enums\ViewPaths\Admin\DealOfTheDay;
use App\Enums\ViewPaths\Admin\Deals;
use App\Enums\ViewPaths\Admin\DeliveryMan;
use App\Enums\ViewPaths\Admin\DeliveryManCash;
use App\Enums\ViewPaths\Admin\DeliverymanWithdraw;
use App\Enums\ViewPaths\Admin\DeliveryRestriction;
use App\Enums\ViewPaths\Admin\Department;
use App\Enums\ViewPaths\Admin\EmailTemplate;
use App\Enums\ViewPaths\Admin\EmergencyContact;
use App\Enums\ViewPaths\Admin\Employee;
use App\Enums\ViewPaths\Admin\EnvironmentSettings;
use App\Enums\ViewPaths\Admin\ErrorLogs;
use App\Enums\ViewPaths\Admin\ExtraCharges;
use App\Enums\ViewPaths\Admin\FeatureDeal;
use App\Enums\ViewPaths\Admin\FeaturesSection;
use App\Enums\ViewPaths\Admin\FileManager;
use App\Enums\ViewPaths\Admin\FirebaseOTPVerification;
use App\Enums\ViewPaths\Admin\FlashDeal;
use App\Enums\ViewPaths\Admin\GoogleMapAPI;
use App\Enums\ViewPaths\Admin\HelpTopic;
use App\Enums\ViewPaths\Admin\InhouseProductSale;
use App\Enums\ViewPaths\Admin\InhouseShop;
use App\Enums\ViewPaths\Admin\InvoiceSettings;
use App\Enums\ViewPaths\Admin\Language;
use App\Enums\ViewPaths\Admin\Leads;
use App\Enums\ViewPaths\Admin\Mail;
use App\Enums\ViewPaths\Admin\MostDemanded;
use App\Enums\ViewPaths\Admin\Notification;
use App\Enums\ViewPaths\Admin\Notifications;
use App\Enums\ViewPaths\Admin\NotificationSetup;
use App\Enums\ViewPaths\Admin\OfflinePaymentMethod;
use App\Enums\ViewPaths\Admin\Order;
use App\Enums\ViewPaths\Admin\Pages;
use App\Enums\ViewPaths\Admin\PaymentMethod;
use App\Enums\ViewPaths\Admin\POS;
use App\Enums\ViewPaths\Admin\POSOrder;
use App\Enums\ViewPaths\Admin\PrioritySetup;
use App\Enums\ViewPaths\Admin\Product;
use App\Enums\ViewPaths\Admin\Profile;
use App\Enums\ViewPaths\Admin\PushNotification;
use App\Enums\ViewPaths\Admin\QuotationSettings;
use App\Enums\ViewPaths\Admin\Recaptcha;
use App\Enums\ViewPaths\Admin\RefundRequest;
use App\Enums\ViewPaths\Admin\RefundTransaction;
use App\Enums\ViewPaths\Admin\Review;
use App\Enums\ViewPaths\Admin\RobotsMetaContent;
use App\Enums\ViewPaths\Admin\SEOSettings;
use App\Enums\ViewPaths\Admin\ShippingMethod;
use App\Enums\ViewPaths\Admin\ShippingType;
use App\Enums\ViewPaths\Admin\SiteMap;
use App\Enums\ViewPaths\Admin\SMSModule;
use App\Enums\ViewPaths\Admin\SocialLoginSettings;
use App\Enums\ViewPaths\Admin\SocialMedia;
use App\Enums\ViewPaths\Admin\SocialMediaChat;
use App\Enums\ViewPaths\Admin\SoftwareUpdate;
use App\Enums\ViewPaths\Admin\StockRequest;
use App\Enums\ViewPaths\Admin\StockTransfer;
use App\Enums\ViewPaths\Admin\StorageConnectionSettings;
use App\Enums\ViewPaths\Admin\SubCategory;
use App\Enums\ViewPaths\Admin\SubSubCategory;
use App\Enums\ViewPaths\Admin\SupportTicket;
use App\Enums\ViewPaths\Admin\SystemSetup;
use App\Enums\ViewPaths\Admin\TaskManagement;
use App\Enums\ViewPaths\Admin\ThemeSetup;
use App\Enums\ViewPaths\Admin\Vendor;
use App\Enums\ViewPaths\Admin\VendorRegistrationReason;
use App\Enums\ViewPaths\Admin\VendorRegistrationSetting;
use App\Enums\ViewPaths\Admin\WholeSaler;
use App\Enums\ViewPaths\Admin\WholesalerRegistrationReason;
use App\Enums\ViewPaths\Admin\WholesalerRegistrationSetting;
use App\Enums\ViewPaths\Admin\WholeSalesProducts;
use App\Enums\ViewPaths\Admin\WithdrawalMethod;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Blog\BlogCategoryController;
use App\Http\Controllers\Admin\Blog\BlogController;
use App\Http\Controllers\Admin\Blog\BlogDownloadAppController;
use App\Http\Controllers\Admin\Blog\BlogPrioritySetupController;
use App\Http\Controllers\Admin\Branch\AlertsController;
use App\Http\Controllers\Admin\Branch\BranchChartController;
use App\Http\Controllers\Admin\Branch\BranchController;
use App\Http\Controllers\Admin\Branch\CrmSalesReportController;
use App\Http\Controllers\Admin\Branch\ManageBranchController;
use App\Http\Controllers\Admin\Branch\ProductInventoryController;
use App\Http\Controllers\Admin\Branch\ProductStockController;
use App\Http\Controllers\Admin\Branch\StockMovementController;
use App\Http\Controllers\Admin\Branch\StockTransferReportController;
use App\Http\Controllers\Admin\CategoryShippingCostController;
use App\Http\Controllers\Admin\ChattingController;
use App\Http\Controllers\Admin\ClaimWorkflowController;
use App\Http\Controllers\Admin\Cms\AboutController;
use App\Http\Controllers\Admin\Cms\CancellationPolicyController;
use App\Http\Controllers\Admin\Cms\CareerController;
use App\Http\Controllers\Admin\Cms\ContactUsController;
use App\Http\Controllers\Admin\Cms\ContentManagementController;
use App\Http\Controllers\Admin\Cms\HomeController;
use App\Http\Controllers\Admin\Cms\ProductCmsController;
use App\Http\Controllers\Admin\Cms\RefundPolicyController;
use App\Http\Controllers\Admin\Cms\ReturnPolicyController;
use App\Http\Controllers\Admin\Cms\ServiceCmsController;
use App\Http\Controllers\Admin\Crm\CalendarController;
use App\Http\Controllers\Admin\Crm\CrmDashboardController;
use App\Http\Controllers\Admin\Crm\DashboardChartController;
use App\Http\Controllers\Admin\Crm\DealController;
use App\Http\Controllers\Admin\Crm\InboxMessageController;
use App\Http\Controllers\Admin\Crm\LeadController;
use App\Http\Controllers\Admin\CrmAgentSalesMatrixReportController;
use App\Http\Controllers\Admin\CrmEmployeeChannelAssignmentReportController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\Customer\CustomerLoyaltyController;
use App\Http\Controllers\Admin\Customer\CustomerWalletController;
use App\Http\Controllers\Admin\CrmDealSalesReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Deliveryman\DeliveryManCashCollectController;
use App\Http\Controllers\Admin\Deliveryman\DeliveryManController;
use App\Http\Controllers\Admin\Deliveryman\DeliverymanWithdrawController;
use App\Http\Controllers\Admin\Deliveryman\EmergencyContactController;
use App\Http\Controllers\Admin\Department\DepartmentController;
use App\Http\Controllers\Admin\EmailTemplatesController;
use App\Http\Controllers\Admin\Employee\CustomRoleController;
use App\Http\Controllers\Admin\Employee\EmployeeController;
use App\Http\Controllers\Admin\HelpAndSupport\CareerTicketController;
use App\Http\Controllers\Admin\HelpAndSupport\ComplaintController;
use App\Http\Controllers\Admin\HelpAndSupport\ContactController;
use App\Http\Controllers\Admin\HelpAndSupport\HelpTopicController;
use App\Http\Controllers\Admin\HelpAndSupport\ServiceTicketController;
use App\Http\Controllers\Admin\HelpAndSupport\SupportTicketController;
use App\Http\Controllers\Admin\InhouseProductSaleController;
use App\Http\Controllers\Admin\Notification\NotificationController;
use App\Http\Controllers\Admin\Notification\NotificationSetupController;
use App\Http\Controllers\Admin\Notification\PushNotificationSettingsController;
use App\Http\Controllers\Admin\Order\BostaWebhookController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\Order\RefundController;
use App\Http\Controllers\Admin\Order\ShippingAjaxController;
use App\Http\Controllers\Admin\OrderReportController;
use App\Http\Controllers\Admin\Payment\OfflinePaymentMethodController;
use App\Http\Controllers\Admin\POS\CartController;
use App\Http\Controllers\Admin\POS\POSController;
use App\Http\Controllers\Admin\POS\POSOrderController;
use App\Http\Controllers\Admin\Product\AttributeController;
use App\Http\Controllers\Admin\Product\BrandController;
use App\Http\Controllers\Admin\Product\CategoryController;
use App\Http\Controllers\Admin\Product\ExtraChargesController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\Product\ReviewController;
use App\Http\Controllers\Admin\Product\StockRequestController;
use App\Http\Controllers\Admin\Product\StockTransferController;
use App\Http\Controllers\Admin\Product\SubCategoryController;
use App\Http\Controllers\Admin\Product\SubSubCategoryController;
use App\Http\Controllers\Admin\ProductReportController;
use App\Http\Controllers\Admin\ProductStockReportController;
use App\Http\Controllers\Admin\ProductWishlistReportController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Promotion\AllPagesBannerController;
use App\Http\Controllers\Admin\Promotion\BannerController;
use App\Http\Controllers\Admin\Promotion\ClearanceSaleController;
use App\Http\Controllers\Admin\Promotion\ClearanceSalePrioritySetupController;
use App\Http\Controllers\Admin\Promotion\ClearanceSaleVendorOfferController;
use App\Http\Controllers\Admin\Promotion\CouponController;
use App\Http\Controllers\Admin\Promotion\DealOfTheDayController;
use App\Http\Controllers\Admin\Promotion\FeaturedDealController;
use App\Http\Controllers\Admin\Promotion\FlashDealController;
use App\Http\Controllers\Admin\Promotion\MostDemandedController;
use App\Http\Controllers\Admin\Report\RefundTransactionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\Settings\AddonController;
use App\Http\Controllers\Admin\Settings\BusinessSettingsController;
use App\Http\Controllers\Admin\Settings\CurrencyController;
use App\Http\Controllers\Admin\Settings\DatabaseSettingController;
use App\Http\Controllers\Admin\Settings\DeliverymanSettingsController;
use App\Http\Controllers\Admin\Settings\DeliveryRestrictionController;
use App\Http\Controllers\Admin\Settings\EnvironmentSettingsController;
use App\Http\Controllers\Admin\Settings\ErrorLogsController;
use App\Http\Controllers\Admin\Settings\FeaturesSectionController;
use App\Http\Controllers\Admin\Settings\FileManagerController;
use App\Http\Controllers\Admin\Settings\FirebaseOTPVerificationController;
use App\Http\Controllers\Admin\Settings\InhouseShopController;
use App\Http\Controllers\Admin\Settings\InvoiceSettingsController;
use App\Http\Controllers\Admin\Settings\LanguageController;
use App\Http\Controllers\Admin\Settings\OrderSettingsController;
use App\Http\Controllers\Admin\Settings\PagesController;
use App\Http\Controllers\Admin\Settings\PrioritySetupController;
use App\Http\Controllers\Admin\Settings\QuotationSettingsController;
use App\Http\Controllers\Admin\Settings\RobotsMetaContentController;
use App\Http\Controllers\Admin\Settings\SEOSettingsController;
use App\Http\Controllers\Admin\Settings\SiteMapController;
use App\Http\Controllers\Admin\Settings\SocialMediaSettingsController;
use App\Http\Controllers\Admin\Settings\SoftwareUpdateController;
use App\Http\Controllers\Admin\Settings\StateCityController;
use App\Http\Controllers\Admin\Settings\StorageConnectionSettingsController;
use App\Http\Controllers\Admin\Settings\ThemeController;
use App\Http\Controllers\Admin\Settings\VendorRegistrationReasonController;
use App\Http\Controllers\Admin\Settings\VendorRegistrationSettingController;
use App\Http\Controllers\Admin\Settings\VendorSettingsController;
use App\Http\Controllers\Admin\Settings\WholesalerRegistrationReasonController;
use App\Http\Controllers\Admin\Settings\WholesalerRegistrationSettingController;
use App\Http\Controllers\Admin\Shipping\ShippingMethodController;
use App\Http\Controllers\Admin\Shipping\ShippingTypeController;
use App\Http\Controllers\Admin\SlaController;
use App\Http\Controllers\Admin\SystemSetup\SystemLoginSetupController;
use App\Http\Controllers\Admin\TaskManagement\TaskManagementController;
use App\Http\Controllers\Admin\TaskNotificationsController;
use App\Http\Controllers\Admin\ThirdParty\GoogleMapAPIController;
use App\Http\Controllers\Admin\ThirdParty\MailController;
use App\Http\Controllers\Admin\ThirdParty\PaymentMethodController;
use App\Http\Controllers\Admin\ThirdParty\RecaptchaController;
use App\Http\Controllers\Admin\ThirdParty\SMSModuleController;
use App\Http\Controllers\Admin\ThirdParty\SocialLoginSettingsController;
use App\Http\Controllers\Admin\ThirdParty\SocialMediaChatController;
use App\Http\Controllers\Admin\ThirdParty\UcmConfigController;
use App\Http\Controllers\Admin\TransactionReportController;
use App\Http\Controllers\Admin\UcmController;
use App\Http\Controllers\Admin\Vendor\VendorController;
use App\Http\Controllers\Admin\Vendor\WithdrawalMethodController;
use App\Http\Controllers\Admin\VendorProductSaleReportController;
use App\Http\Controllers\Admin\WarrantyClaimChartController;
use App\Http\Controllers\Admin\WarrantyClaimController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Admin\WarrantyDashboardController;
use App\Http\Controllers\Admin\WarrantyTransferController;
use App\Http\Controllers\Admin\WholeSaler\WholesaleDashboardController;
use App\Http\Controllers\Admin\WholeSaler\WholeSaleProductController;
use App\Http\Controllers\Admin\WholeSaler\WholeSalerController;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\SharedController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/reports/unified', [BranchChartController::class, 'index'])->name('admin.reports.unified');
Route::get('/admin/reports/crm', [BranchChartController::class, 'agentCRMReport'])->name('admin.reports.crm');
Route::get('/admin/branch/sales', [BranchChartController::class, 'index'])->name('admin.branch.sales-chart');
Route::post('/admin/branch/sales-chart-data', [BranchChartController::class, 'getChartData'])
    ->name('admin.branch.sales-chart-data');
Route::post('/admin/branch/sales-export', [BranchChartController::class, 'export'])
    ->name('admin.branch.sales-export');

Route::get('/admin/stock/transfer-report', [StockTransferReportController::class, 'index'])->name('admin.stock.transfer-report');
Route::post('/admin/stock/transfer-report-data', [StockTransferReportController::class, 'getTransferData'])
    ->name('admin.stock.transfer-report-data');
Route::get('/admin/crm/sales-report', [CrmSalesReportController::class, 'index'])->name('admin.crm.sales-report');
Route::post('/admin/crm/sales-report-data', [CrmSalesReportController::class, 'getSalesData'])
    ->name('admin.crm.sales-report-data');

//Webhook
Route::post('/bosta/webhook', [BostaWebhookController::class, 'handle'])
    ->withoutMiddleware(['auth', 'verified']) // Remove web auth
    ->name('bosta.webhook');
Route::get('/admin/get-vehicle-models/{make_id}', function ($make_id) {
    return \App\Models\VehicleModel::where('make_id', $make_id)
        ->orderBy('name')
        ->get(['id', 'name']);
});
Route::get('/admin/products/get-models-by-makes', [ProductController::class, 'getModelsByMakes'])->name('admin.products.get-models-by-makes');

Route::controller(SharedController::class)->group(function () {
    Route::post('change-language', 'changeLanguage')->name('change-language');
    Route::post('get-session-recaptcha-code', 'getSessionRecaptchaCode')->name('get-session-recaptcha-code');
    Route::post('g-recaptcha-response-store', 'storeRecaptchaResponse')->name('g-recaptcha-response-store');
});

Route::controller(FirebaseController::class)->group(function () {
    Route::post('system/subscribe-to-topic', 'subscribeToTopic')->name('system.subscribeToTopic');
});


Route::group(['prefix' => 'login'], function () {
    Route::get('{loginUrl}', [LoginController::class, 'index']);
    Route::get('recaptcha/{tmp}', [LoginController::class, 'generateReCaptcha'])->name('recaptcha');
    Route::post('/', [LoginController::class, 'login'])->name('login');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::controller(DashboardController::class)->group(function () {

            Route::get(Dashboard::VIEW[URI], 'index')->name('index');
            Route::post(Dashboard::ORDER_STATUS[URI], 'getOrderStatus')->name('order-status');
            Route::get(Dashboard::EARNING_STATISTICS[URI], 'getEarningStatistics')->name('earning-statistics');
            Route::get(Dashboard::ORDER_STATISTICS[URI], 'getOrderStatistics')->name('order-statistics');
            Route::get(Dashboard::REAL_TIME_ACTIVITIES[URI], 'getRealTimeActivities')->name('real-time-activities');
        });
    });

    Route::controller(UcmController::class)->group(function () {
        Route::group(['prefix' => 'ucm', 'as' => 'ucm.'], function () {
            Route::get('calls',  'calls')->name('calls');
            Route::post('accept',  'accept')->name('accept');
            Route::post('reject',  'reject')->name('reject');
            Route::post('end',  'end')->name('end');
        });
    });



    Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {

        Route::controller(BlogCategoryController::class)->group(function () {
            Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
                Route::post('add', 'add')->name('add');
                Route::post('category-info', 'getCategoryInfo')->name('info');
                Route::post('update', 'update')->name('update');
                Route::get('status', 'updateStatus')->name('status-update');
                Route::delete('delete', 'deleteCategory')->name('delete');
                Route::post('search', 'search')->name('search');
                Route::get('get-list', 'getList')->name('get-list');
            });
        });

        Route::controller(BlogController::class)->group(function () {
            Route::get('view', 'index')->name('view');
            Route::post('intro', 'updateIntro')->name('intro');
            Route::get('add', 'getAddView')->name('add');
            Route::post('add', 'addBlog')->name('store');
            Route::get('edit', 'getUpdateView')->name('edit');
            Route::post('update', 'update')->name('update');
            Route::post('status-update', 'updateStatus')->name('status-update');
            Route::post('blog-status-update' . '/{id}', 'updateBlogStatus')->name('blog-status-update');
            Route::get('draft-edit' . '/{id}', 'draftEdit')->name('draft-edit');
            Route::post('delete', 'delete')->name('delete');
            Route::post('section-view', 'sectionView')->name('section-view');
        });

        Route::controller(BlogDownloadAppController::class)->group(function () {
            Route::get('app-download-setup', 'appDownloadSetup')->name('app-download-setup');
            Route::post('app-download-setup', 'updateDownloadAppButton');
            Route::post('app-download-setup-status', 'updateStatus')->name('app-download-setup-status');
            Route::post('delete-image', 'deleteImage')->name('delete-image');
        });

        Route::group(['prefix' => 'priority-setup', 'as' => 'priority-setup.'], function () {
            Route::controller(BlogPrioritySetupController::class)->group(function () {
                Route::get('', 'index')->name('index');
                Route::post('', 'update');
            });
        });
    });

    Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {

        // READ permission routes (mostly GET and some ANY for views)
        Route::middleware('module:pos_management,read')->group(function () {

            Route::controller(POSController::class)->group(function () {
                Route::get(POS::INDEX[URI], 'index')->name('index');
                Route::get(POS::QUICK_VIEW[URI], 'getQuickView')->name('quick-view');
                Route::get(POS::SEARCH[URI], 'getSearchedProductsView')->name('search-product');
                Route::any(POS::CHANGE_CUSTOMER[URI], 'changeCustomer')->name('change-customer');  // Reading or selecting customer
            });

            Route::controller(CartController::class)->group(function () {
                Route::get(Cart::GET_CART_IDS[URI], 'getCartIds')->name('get-cart-ids');
                Route::get(Cart::CLEAR_CART_IDS[URI], 'clearSessionCartIds')->name('clear-cart-ids');
                Route::any(Cart::CART_EMPTY[URI], 'emptyCart')->name('empty-cart');
                Route::any(Cart::CHANGE_CART[URI], 'changeCart')->name('change-cart');
                Route::get(Cart::NEW_CART_ID[URI], 'addNewCartId')->name('new-cart-id');
            });

            Route::controller(POSOrderController::class)->group(function () {
                Route::any(POSOrder::HOLD_ORDERS[URI], 'getAllHoldOrdersView')->name('view-hold-orders');
                Route::any(POSOrder::CANCEL_ORDER[URI], 'cancelOrder')->name('cancel-order');
            });
        });

        // CREATE permission routes (adding something new)
        Route::middleware('module:pos_management,create')->group(function () {

            Route::controller(CartController::class)->group(function () {
                Route::post(Cart::ADD[URI], 'addToCart')->name('add-to-cart');
            });

            Route::controller(POSOrderController::class)->group(function () {
                Route::post(POSOrder::ORDER_PLACE[URI], 'placeOrder')->name('place-order');
            });
        });

        // UPDATE permission routes (modifying existing data)
        Route::middleware('module:pos_management,update')->group(function () {

            Route::controller(POSController::class)->group(function () {
                Route::post(POS::UPDATE_DISCOUNT[URI], 'updateDiscount')->name('update-discount');
                Route::post(POS::COUPON_DISCOUNT[URI], 'getCouponDiscount')->name('coupon-discount');
            });

            Route::controller(CartController::class)->group(function () {
                Route::post(Cart::VARIANT[URI], 'getVariantPrice')->name('get-variant-price');
                Route::post(Cart::QUANTITY_UPDATE[URI], 'updateQuantity')->name('update-quantity');
                Route::post(Cart::REMOVE[URI], 'removeCart')->name('remove-cart');
            });

            Route::controller(POSOrderController::class)->group(function () {
                Route::post(POSOrder::ORDER_DETAILS[URI] . '/{id}', 'index')->name('order-details');
            });
        });
    });

    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get(Profile::INDEX[URI], 'index')->name('index');
            Route::get(Profile::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Profile::UPDATE[URI] . '/{id}', 'update');
            Route::patch(Profile::UPDATE[URI] . '/{id}', 'updatePassword');
        });
    });
    Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get(Product::LIST[URI] . '/{type}', 'index')->middleware('module:product_management,product_list')->name('list');
            Route::get(Product::VIEW[URI] . '/{addedBy}/{id}', 'getView')->middleware('module:product_management,product_view')->name('view');
            Route::get(Product::GET_CATEGORIES[URI], 'getCategories')->name('get-categories');
            Route::get(Product::GET_SUB_CATEGORIES[URI], 'getSubCategories')->name('get-sub-categories');
            Route::get(Product::GET_ATTRIBUTES[URI] . '/{id}', 'getProductAttributes')->name('get-attributes-for-product');
            Route::get(Product::GET_UNIT_PRICE[URI] . '/{id}', 'getUnitPrice')->name('get-unit-price');
            Route::get(Product::BARCODE_VIEW[URI] . '/{id}', 'getBarcodeView')->name('barcode');
            Route::get(Product::EXPORT_EXCEL[URI] . '/{type}', 'exportList')->name('export-excel');
            Route::get(Product::STOCK_LIMIT[URI] . '/{type}', 'getStockLimitListView')->name('stock-limit-list');
            Route::get(Product::STOCK_LIMIT_PRODUCTS[URI] . '/{type}', 'getStockLimitListViewProducts')->name('stock-limit-products');
            Route::get(Product::UPDATE[URI] . '/{id}', 'getUpdateView')->middleware('module:product_management,product_update')->name('update');
            Route::get(Product::DELETE_IMAGE[URI], 'deleteImage')->name('delete-image');
            Route::get(Product::GET_VARIATIONS[URI], 'getVariations')->name('get-variations');
            Route::get(Product::STOCK_REPORT[URI], 'getStockReport')->name('stock-report');
            Route::get(Product::BULK_IMPORT[URI], 'getBulkImportView')->middleware('module:product_management,bulk_import')->name('bulk-import');
            Route::get(Product::UPDATED_PRODUCT_LIST[URI], 'updatedProductList')->middleware('module:product_management,product_list')->name('updated-product-list');
            Route::get(Product::SEARCH[URI], 'getSearchedProductsView')->name('search-product');
            Route::get(Product::MULTIPLE_PRODUCT_DETAILS[URI], 'getMultipleProductDetailsView')->name('multiple-product-details');
            Route::get(Product::PRODUCT_GALLERY[URI], 'getProductGalleryView')->middleware('module:product_management,product_gallery')->name('product-gallery');
            Route::get(Product::PRODUCT_MAKE[URI], 'getProductMakeView')->middleware('module:product_management,product_make_setup')->name('product-make');
            Route::get(Product::PRODUCT_MAKE_MODEL[URI] . '/{id}', 'getMakeModels')->middleware('module:product_management,product_make_setup')->name('make.models');
            Route::get(Product::STOCK_LIMIT_STATUS[URI] . '/{type}', 'getStockLimitStatus')->name('stock-limit-status');
            Route::get(Product::REQUEST_RESTOCK_LIST[URI], 'getRequestRestockListView')->middleware('module:branch_management,request_restock_list')->name('request-restock-list');
            Route::get(Product::EXPORT_RESTOCK[URI], 'exportRestockList')->middleware('module:branch_management,request_restock_list')->name('restock-export');
            Route::get(Product::GET_PRODUCTS[URI], 'getProducts')->name('get-products');
            Route::get(Product::DOWNLOAD_CSV[URI],  'downloadCsv')->name('download.csv');
        });

        // CREATE permission routes
        Route::middleware('module:product_management,create')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::get(Product::ADD[URI], 'getAddView')->middleware('module:product_management,product_add')->name('add');
                Route::post(Product::ADD[URI], 'add')->middleware('module:product_management,product_add')->name('store');
                Route::post(Product::MAKE_ADD[URI], 'storeOrUpdateMake')->middleware('module:product_management,product_make_setup')->name('make.store');
                Route::post(Product::SKU_COMBINATION[URI], 'getSkuCombinationView')->name('sku-combination');
                Route::post(Product::DIGITAL_VARIATION_COMBINATION[URI], 'getDigitalVariationCombinationView')->name('digital-variation-combination');
                Route::post(Product::DIGITAL_VARIATION_FILE_DELETE[URI], 'deleteDigitalVariationFile')->name('digital-variation-file-delete');
                Route::post(Product::BULK_IMPORT[URI], 'importBulkProduct')->middleware('module:product_management,bulk_import');
            });
        });

        // UPDATE permission routes
        Route::middleware('module:product_management,update')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::post(Product::UPDATE[URI] . '/{id}', 'update')->middleware('module:product_management,product_update');
                Route::post(Product::UPDATE_STATUS[URI], 'updateStatus')->middleware('module:product_management,product_update')->name('status-update');
                Route::post(Product::UPDATE_QUANTITY[URI], 'updateQuantity')->name('update-quantity');
                Route::post(Product::UPDATED_SHIPPING[URI], 'updatedShipping')->name('updated-shipping');
                Route::post(Product::DENY[URI], 'deny')->name('deny');
                Route::post(Product::APPROVE_STATUS[URI], 'approveStatus')->name('approve-status');
                Route::post(Product::PRODUCT_CMS_STATUS[URI], 'updateProductCmsStatus')->middleware('module:product_management,showcase_status')->name('product-cms-status');
                Route::post(Product::PRODUCT_SHOWCASE_STATUS[URI], 'updateProductShowcaseStatus')->middleware('module:product_management,showcase_status')->name('product-showcase-status');
                Route::post(Product::FEATURE_PRODUCT_STATUS[URI], 'updateFeaturedStatus')->middleware('module:product_management,showcase_status')->name('product-featured-status');
                Route::post(Product::DELETE_PREVIEW_FILE[URI], 'deletePreviewFile')->middleware('module:product_management,product_delete')->name('delete-preview-file');
            });
        });

        // DELETE permission routes
        Route::middleware('module:product_management,delete')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::delete(Product::DELETE[URI] . '/{id}', 'delete')->middleware('module:product_management,product_delete')->name('delete');
                Route::delete(Product::RESTOCK_DELETE[URI] . '/{id}', 'deleteRestock')->name('restock-delete');
                Route::delete(Product::MAKE_DELETE[URI]  . '/{id}', 'destroyMake')->middleware('module:product_management,product_make_setup')->name('make.destroy');
            });
        });
    });

    Route::middleware(['admin', 'auth:admin'])->prefix('warranty')->name('warranty.')->group(function () {

        Route::controller(WarrantyDashboardController::class)->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard')->middleware('module:warranty_section,warranty_dashboard');
        });

        Route::controller(WarrantyController::class)->group(function () {
            Route::get('/import', 'importView')->name('import')->middleware('module:warranty_section,warranty_import');
            Route::post('/import', 'import')->name('import')->middleware('module:warranty_section,warranty_import');
            Route::get('/import-history', 'importHistory')->name('import-history')->middleware('module:warranty_section,warranty_import_history');
            Route::get('/import/{date}', 'historyDetails')->name('history-details')->middleware('module:warranty_section,warranty_import_history');
            Route::get('/download-error-csv', 'downloadErrorCsv')->name('download_error_csv')->middleware('module:warranty_section,warranty_import');
            Route::get('/continue-import', 'continueImport')->name('continue-import')->middleware('module:warranty_section,warranty_import');
            Route::get('/reupload', 'reupload')->name('reupload')->middleware('module:warranty_section,warranty_import');
        });
        Route::controller(WarrantyController::class)->group(function () {
            Route::get('/activation/list', 'activationList')->name('activation.list')->middleware('module:warranty_section,warranty_activation_list');
            Route::get('/activation/{warranty}/view', 'activationView')->name('activation.view')->middleware('module:warranty_section,warranty_activation_view');
            Route::get('/activation/manual', 'manualActivateView')->name('activation.manual.view')->middleware('module:warranty_section,warranty_manual_activation');
            Route::post('/activation/manual', 'manualActivate')->name('activation.manual')->middleware('module:warranty_section,warranty_manual_activation');
            Route::get('/review/activation', 'activationReviews')->name('review.activation')->middleware('module:warranty_section,warranty_activation_review');
            Route::post('/review/activation/{review}/approve', 'approveActivation')->name('review.activation.approve')->middleware('module:warranty_section,warranty_activation_approve');
            Route::post('/review/activation/{review}/reject', 'rejectActivation')->name('review.activation.reject')->middleware('module:warranty_section,warranty_activation_reject');
        });


        Route::controller(WarrantyClaimController::class)->group(function () {
            Route::get('/claim/all', 'all')->name('claim.all')->middleware('module:crm_section,warranty_claim_list');
            Route::get('/claim/new', 'new')->name('claim.new')->middleware('module:crm_section,warranty_claim_list');
            Route::get('/claim/triage-pending', 'triagePending')->name('claim.triage-pending')->middleware('module:crm_section,warranty_claim_triage');
            Route::get('/claim/approved', 'approved')->name('claim.approved')->middleware('module:crm_section,warranty_claim_approved');
            Route::get('/claim/rma-issued', 'rmaIssued')->name('claim.rma-issued')->middleware('module:crm_section,warranty_claim_rma');
            Route::get('/claim/received', 'received')->name('claim.received')->middleware('module:crm_section,warranty_claim_received');
            Route::get('/claim/repair-pending', 'repairPending')->name('claim.repair-pending')->middleware('module:crm_section,warranty_claim_repair');
            Route::get('/claim/resolved', 'resolved')->name('claim.resolved')->middleware('module:crm_section,warranty_claim_resolved');
            Route::get('/claim/closed', 'closed')->name('claim.closed')->middleware('module:crm_section,warranty_claim_closed');
            Route::get('/claim/rejected', 'rejected')->name('claim.rejected')->middleware('module:crm_section,warranty_claim_rejected');
            Route::get('/claim/{claim}/view', 'view')->name('claim.view')->middleware('module:crm_section,warranty_claim_view');
        });
        Route::controller(WarrantyClaimController::class)->group(function () {
            Route::post('/claim/{claim}/decide', 'decide')->name('claim.decide')->middleware('module:crm_section,warranty_claim_decide');
            Route::post('/claim/{claim}/receive', 'receive')->name('claim.receive')->middleware('module:crm_section,warranty_claim_receive');
            Route::post('/claim/{claim}/diagnose', 'diagnose')->name('claim.diagnose')->middleware('module:crm_section,warranty_claim_diagnose');
            Route::post('/claim/{claim}/repair-complete', 'repairComplete')->name('claim.repair-complete')->middleware('module:crm_section,warranty_claim_repair');
            Route::post('/claim/{claim}/qc-pass', 'qcPass')->name('claim.qc-pass')->middleware('module:crm_section,warranty_claim_qc');
            Route::post('/claim/{claim}/dispatch', 'markDispatch')->name('claim.dispatch')->middleware('module:crm_section,warranty_claim_dispatch');
            Route::post('/claim/{claim}/replacement-commit', 'replacementCommit')->name('claim.replacement-commit')->middleware('module:crm_section,warranty_claim_replacement');
            Route::post('/claim/{claim}/close', 'close')->name('claim.close')->middleware('module:crm_section,warranty_claim_close');
            Route::post('/claim/{claim}/payment-handle', 'paymentHandle')->name('claim.payment-handle')->middleware('module:crm_section,warranty_claim_payment');
            Route::post('/claim/{claim}/issue-rma', 'issueRma')->name('claim.issue-rma')->middleware('module:crm_section,warranty_claim_rma');
            Route::post('/claim/{claim}/resume', 'resume')->name('claim.resume')->middleware('module:crm_section,warranty_claim_resume');
            Route::post('/claim/{claim}/resolve', 'resolve')->name('claim.resolve')->middleware('module:crm_section,warranty_claim_resolve');
            Route::post('/claim/submit', 'submit')->name('claim.submit')->middleware('module:crm_section,warranty_claim_submit');
            Route::get('/claim/export', 'export')->name('claim.export')->middleware('module:crm_section,warranty_claim_export');
        });

        Route::controller(WarrantyController::class)->group(function () {
            Route::get('/review/claim', 'claimReviews')->name('review.claim')->middleware('module:warranty_section,warranty_claim_review');
        });
        Route::controller(WarrantyController::class)->group(function () {
            Route::get('/blacklist', 'blacklistView')->name('blacklist')->middleware('module:warranty_section,warranty_blacklist');
            Route::get('/blacklist/add', 'blacklistAddView')->name('blacklist.add')->middleware('module:warranty_section,warranty_blacklist_add');
            Route::post('/blacklist', 'blacklist')->name('blacklist.store')->middleware('module:warranty_section,warranty_blacklist_add');

            Route::delete('/blacklist/{id}', 'blacklistRemove')->name('blacklist.remove')->middleware('module:warranty_section,warranty_blacklist_remove');
        });
        Route::controller(WarrantyController::class)->group(function () {
            Route::get('/report/claims', 'reportClaims')->name('report.claims')->middleware('module:warranty_section,warranty_report_claims');
            Route::get('/report/sla', 'reportSLA')->name('report.sla')->middleware('module:warranty_section,warranty_report_sla');
            Route::get('/report/activations', 'reportActivations')->name('report.activations')->middleware('module:warranty_section,warranty_report_activations');
        });


        Route::controller(WarrantyTransferController::class)->group(function () {
            Route::get('/serial-transaction/list', 'list')->name('serial-transaction.list')->middleware('module:warranty_section,warranty_serial_transaction');
            Route::get('/serial-transaction/history/{serial}', 'historyModal')->name('serial-transaction.history-modal')->middleware('module:warranty_section,warranty_serial_transaction');
        });
        Route::controller(WarrantyClaimChartController::class)->group(function () {
            Route::get('/claim-chart', 'index')->name('claim.chart');
            Route::get('/claim-chart-data', 'getChartData')->name('claim.chart.data');
            Route::get('/claim-table-data', 'getTableData')->name('claim.table.data');
            // Export routes
            Route::get('/claim-export-excel', 'exportExcel')->name('claim.export.excel');
            Route::get('/claim-export-pdf', 'exportPdf')->name('claim.export.pdf');
        });
    });
    Route::group(['prefix' => 'stock-request', 'as' => 'stock-request.'], function () {

        // READ permission routes
        Route::controller(StockRequestController::class)->group(function () {
            Route::get(StockRequest::LIST[URI], 'index')->middleware('module:branch_management,stock_request_list')->name('list');
            Route::get(StockRequest::VIEW[URI] . '/{id}', 'getStockRequestView')->middleware('module:branch_management,stock_request_view')->name('view');
            Route::get(StockRequest::TRANSFER_QUICK_VIEW[URI], 'getQuickView')->middleware('module:branch_management,stock_delivery')->name('quick-view');
            Route::get(StockRequest::PRODUCT_STOCK[URI], 'getBranchesProductStock')->middleware('module:branch_management,stock_delivery')->name('get-branches-product-stock');
            Route::get(StockRequest::SEARCH[URI], 'getProductByCategory')->middleware('module:branch_management,stock_request_view')->name('search-product');
        });

        // CREATE permission routes
        Route::controller(StockRequestController::class)->group(function () {
            Route::get(StockRequest::ADD[URI], 'addStockRequestListView')->middleware('module:branch_management,add_new_stock_request')->name('add');
            Route::post(StockRequest::ADD[URI], 'saveStockRequest')->middleware('module:branch_management,add_new_stock_request')->name('store');
        });

        // UPDATE permission routes
        Route::controller(StockRequestController::class)->group(function () {
            Route::post(StockRequest::UPDATE_STOCK_REQUEST[URI], 'updateProductStockRequestStatus')->middleware('module:branch_management,add_new_stock_request')->name('stock-request-update');
        });
    });

    Route::group(['prefix' => 'stock-transfer', 'as' => 'stock-transfer.'], function () {

        // READ permission routes
        Route::controller(StockTransferController::class)->group(function () {
            Route::get(StockTransfer::LIST[URI], 'index')->middleware('module:branch_management,stock_transfer_list')->name('list');
            Route::get(StockTransfer::DOWNLOAD_ERROR_CSV[URI], 'downloadErrorCsv')->middleware('module:branch_management,stock_transfer_list')->name('download-error-csv');
            Route::get(StockRequest::VIEW[URI] . '/{id}', 'getStockRequestView')->middleware('module:branch_management,transfer_new_stock')->name('view');
            Route::get(StockRequest::SEARCH[URI], 'getStockSearchedProductsView')->middleware('module:branch_management,transfer_new_stock')->name('search-products');
            Route::get(StockRequest::TRANSFER_QUICK_VIEW[URI], 'getQuickView')->middleware('module:branch_management,transfer_new_stock')->name('quick-view');
            Route::get(StockRequest::PRODUCT_STOCK[URI], 'getBranchesProductStock')->middleware('module:branch_management,transfer_new_stock')->name('get-branches-product-stock');
            Route::get(StockRequest::BRANCH_PRODUCT_STOCK[URI], 'getStock')->middleware('module:branch_management,transfer_new_stock')->name('get-stock');
            Route::get(StockRequest::DOWNLOAD_CSV[URI] . '/{id}', 'downloadCsv')->middleware('module:branch_management,transfer_new_stock')->name('download-csv');
        });


        // CREATE permission routes
        Route::controller(StockTransferController::class)->group(function () {
            Route::get(StockTransfer::ADD[URI], 'addStockTransferListView')->middleware('module:branch_management,transfer_new_stock')->name('add');
            Route::post(StockTransfer::ADD[URI], 'saveStockTransfer')->middleware('module:branch_management,transfer_new_stock')->name('store');
        });

        // UPDATE permission routes
        Route::controller(StockTransferController::class)->group(function () {
            Route::post(StockRequest::UPDATE_STOCK_REQUEST[URI], 'updateProductStockRequestStatus')->middleware('module:branch_management,transfer_new_stock')->name('stock-request-update');
        });
    });

    Route::group(['prefix' => 'extra-charges', 'as' => 'extra-charges.'], function () {

        // READ permission
        Route::middleware('module:product_management,read')->group(function () {
            Route::controller(ExtraChargesController::class)->group(function () {
                Route::get(ExtraCharges::LIST[URI] . '/{type}', 'index')->name('list');
            });
        });

        // CREATE permission
        Route::middleware('module:product_management,create')->group(function () {
            Route::controller(ExtraChargesController::class)->group(function () {
                Route::post(ExtraCharges::ADD[URI], 'add')->name('add');
            });
        });

        // UPDATE permission
        Route::middleware('module:product_management,update')->group(function () {
            Route::controller(ExtraChargesController::class)->group(function () {
                Route::post(ExtraCharges::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
            });
        });

        // DELETE permission
        Route::middleware('module:product_management,delete')->group(function () {
            Route::controller(ExtraChargesController::class)->group(function () {
                Route::post(ExtraCharges::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {

        Route::controller(OrderController::class)->group(function () {
            Route::get(Order::LIST[URI] . '/{status}', 'index')->middleware('module:order_management,order_list')->name('list');
            Route::get(Order::EXPORT_EXCEL[URI] . '/{status}', 'exportList')->middleware('module:order_management,order_list')->name('export-excel');
            Route::get(Order::VIEW[URI] . '/{id}', 'getView')->middleware('module:order_management,order_view')->name('details');
            Route::get(Order::CUSTOMERS[URI], 'getCustomers')->name('customers');
            Route::get(Order::IN_HOUSE_ORDER_FILTER[URI], 'filterInHouseOrder')->middleware('module:order_management,order_list')->name('inhouse-order-filter');
        });

        // CREATE permission (includes invoice generation, file upload etc.)
        Route::controller(OrderController::class)->group(function () {
            Route::get(Order::GENERATE_INVOICE[URI] . '/{id}', 'generateInvoice')->middleware('module:order_management,order_invoice')->name('generate-invoice');
            Route::post(Order::DIGITAL_FILE_UPLOAD_AFTER_SELL[URI], 'uploadDigitalFileAfterSell')->name('digital-file-upload-after-sell');
        });

        // UPDATE permission
        Route::controller(OrderController::class)->group(function () {
            Route::post(Order::UPDATE_ADDRESS[URI], 'updateAddress')->middleware('module:order_management,order_address')->name('address-update');
            Route::post(Order::UPDATE_DELIVERY_INFO[URI], 'updateDeliverInfo')->middleware('module:order_management,order_shiping_method')->name('update-deliver-info');
            Route::post(Order::UPDATE_EXCHANGE_INFO[URI], 'updateExchangeInfo')->name('update-exchange-info');
            Route::get(Order::ADD_DELIVERY_MAN[URI] . '/{order_id}/{d_man_id}', 'addDeliveryMan')->middleware('module:order_management,order_shiping_method')->name('add-delivery-man');
            Route::post(Order::UPDATE_AMOUNT_DATE[URI], 'updateAmountDate')->middleware('module:order_management,order_payment_status')->name('amount-date-update');
            Route::post(Order::PAYMENT_STATUS[URI], 'updatePaymentStatus')->middleware('module:order_management,order_payment_status')->name('payment-status');
            Route::post(Order::UPDATE_STATUS[URI], 'updateStatus')->middleware('module:order_management,order_deliverey')->name('status');
            Route::post(Order::UPDATE_DELIVERY_BRANCH[URI], 'updateTransferDeliverBranch')->middleware('module:order_management,order_branch_assign')->name('transfer-delivered-branch');
        });
    });

    // Attribute
    Route::group(['prefix' => 'attribute', 'as' => 'attribute.'], function () {

        // READ
        Route::middleware('module:product_management,product_attribute_setup')->group(function () {
            Route::controller(AttributeController::class)->group(function () {
                Route::get(Attribute::LIST[URI], 'index')->name('view');
                Route::get(Attribute::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            });
        });

        // CREATE
        Route::middleware('module:product_management,product_attribute_setup')->group(function () {
            Route::controller(AttributeController::class)->group(function () {
                Route::post(Attribute::STORE[URI], 'add')->name('store');
            });
        });

        // UPDATE
        Route::middleware('module:product_management,product_attribute_setup')->group(function () {
            Route::controller(AttributeController::class)->group(function () {
                Route::post(Attribute::UPDATE[URI] . '/{id}', 'update');
            });
        });

        // DELETE
        Route::middleware('module:product_management,product_attribute_setup')->group(function () {
            Route::controller(AttributeController::class)->group(function () {
                Route::post(Attribute::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    // Brand
    Route::group(['prefix' => 'brand', 'as' => 'brand.'], function () {

        // READ
        Route::middleware('module:product_management,brand_setup')->group(function () {
            Route::controller(BrandController::class)->group(function () {
                Route::get(Brand::LIST[URI], 'index')->name('list');
                Route::get(Brand::ADD[URI], 'getAddView')->name('add-new');
                Route::get(Brand::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::get(Brand::EXPORT[URI], 'exportList')->name('export');
            });
        });

        // CREATE
        Route::middleware('module:product_management,brand_setup')->group(function () {
            Route::controller(BrandController::class)->group(function () {
                Route::post(Brand::ADD[URI], 'add');
            });
        });

        // UPDATE
        Route::middleware('module:product_management,brand_setup')->group(function () {
            Route::controller(BrandController::class)->group(function () {
                Route::post(Brand::UPDATE[URI] . '/{id}', 'update');
                Route::post(Brand::STATUS[URI], 'updateStatus')->name('status-update');
            });
        });

        // DELETE
        Route::middleware('module:product_management,brand_setup')->group(function () {
            Route::controller(BrandController::class)->group(function () {
                Route::post(Brand::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    // Category
    Route::group(['prefix' => 'category', 'as' => 'category.'], function () {

        // READ
        Route::middleware('module:product_management,category_setup')->group(function () {
            Route::controller(CategoryController::class)->group(function () {
                Route::get(Category::LIST[URI], 'index')->name('view');
                Route::get(Category::UPDATE[URI], 'getUpdateView')->name('update');
                Route::get(Category::EXPORT[URI], 'getExportList')->name('export');
            });
        });

        // CREATE
        Route::middleware('module:product_management,category_setup')->group(function () {
            Route::controller(CategoryController::class)->group(function () {
                Route::post(Category::ADD[URI], 'add')->name('store');
            });
        });

        // UPDATE
        Route::middleware('module:product_management,category_setup')->group(function () {
            Route::controller(CategoryController::class)->group(function () {
                Route::put(Category::UPDATE[URI], 'update');
                Route::post(Category::STATUS[URI], 'updateStatus')->name('status');
            });
        });

        // DELETE
        Route::middleware('module:product_management,category_setup')->group(function () {
            Route::controller(CategoryController::class)->group(function () {
                Route::post(Category::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    // Sub Category
    Route::group(['prefix' => 'sub-category', 'as' => 'sub-category.'], function () {

        // READ
        Route::middleware('module:product_management,sub_category_setup')->group(function () {
            Route::controller(SubCategoryController::class)->group(function () {
                Route::get(SubCategory::LIST[URI], 'index')->name('view');
                Route::get(SubCategory::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::get(SubCategory::EXPORT[URI], 'getExportList')->name('export');
            });
        });

        // CREATE
        Route::middleware('module:product_management,sub_category_setup')->group(function () {
            Route::controller(SubCategoryController::class)->group(function () {
                Route::post(SubCategory::ADD[URI], 'add')->name('store');
            });
        });

        // UPDATE
        Route::middleware('module:product_management,sub_category_setup')->group(function () {
            Route::controller(SubCategoryController::class)->group(function () {
                Route::put(SubCategory::UPDATE[URI] . '/{id}', 'update');
                Route::post(SubCategory::EXTRACHARGE_STATUS[URI], 'updateExtraChargeStatus')->name('updateExtraChargeStatus');
            });
        });

        // DELETE
        Route::middleware('module:product_management,sub_category_setup')->group(function () {
            Route::controller(SubCategoryController::class)->group(function () {
                Route::post(SubCategory::DELETE[URI], 'delete')->name('delete');
            });
        });
    });

    // Sub Sub Category
    Route::group(['prefix' => 'sub-sub-category', 'as' => 'sub-sub-category.'], function () {

        // READ
        Route::middleware('module:product_management,sub_sub_category_setup')->group(function () {
            Route::controller(SubSubCategoryController::class)->group(function () {
                Route::get(SubSubCategory::LIST[URI], 'index')->name('view');
                Route::get(SubSubCategory::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::get(SubSubCategory::EXPORT[URI], 'getExportList')->name('export');
                Route::post(SubSubCategory::GET_SUB_CATEGORY[URI], 'getSubCategory')->name('getSubCategory');
            });
        });

        // CREATE
        Route::middleware('module:product_management,sub_sub_category_setup')->group(function () {
            Route::controller(SubSubCategoryController::class)->group(function () {
                Route::post(SubSubCategory::ADD[URI], 'add')->name('store');
            });
        });

        // UPDATE
        Route::middleware('module:product_management,sub_sub_category_setup')->group(function () {
            Route::controller(SubSubCategoryController::class)->group(function () {
                Route::post(SubSubCategory::UPDATE[URI] . '/{id}', 'update');
            });
        });

        // DELETE
        Route::middleware('module:product_management,sub_sub_category_setup')->group(function () {
            Route::controller(SubSubCategoryController::class)->group(function () {
                Route::post(SubSubCategory::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    // Banner
    Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {

        // READ
        Route::middleware('module:promotion_management,banner_setup')->group(function () {
            Route::controller(BannerController::class)->group(function () {
                Route::get(Banner::LIST[URI], 'index')->name('list');
                Route::get(Banner::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            });
        });

        // CREATE
        Route::middleware('module:promotion_management,banner_setup')->group(function () {
            Route::controller(BannerController::class)->group(function () {
                Route::post(Banner::ADD[URI], 'add')->name('store');
            });
        });

        // UPDATE
        Route::middleware('module:promotion_management,banner_setup')->group(function () {
            Route::controller(BannerController::class)->group(function () {
                Route::post(Banner::UPDATE[URI] . '/{id}', 'update');
                Route::post(Banner::STATUS[URI], 'updateStatus')->name('status');
            });
        });

        // DELETE
        Route::middleware('module:promotion_management,banner_setup')->group(function () {
            Route::controller(BannerController::class)->group(function () {
                Route::post(Banner::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {

        Route::middleware('module:user_section,read')->group(function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::get(Customer::LIST[URI], 'getListView')->name('list');
                Route::get(Customer::VIEW[URI] . '/{user_id}', 'getView')->name('view');
                Route::get(Customer::ORDER_LIST_EXPORT[URI] . '/{user_id}', 'exportOrderList')->name('order-list-export');
                Route::get(Customer::SUBSCRIBER_LIST[URI], 'getSubscriberListView')->name('subscriber-list');
                Route::get(Customer::SUBSCRIBER_EXPORT[URI], 'exportSubscribersList')->name('subscriber-list.export');
                Route::get(Customer::EXPORT[URI], 'exportList')->name('export');
                Route::get(Customer::SEARCH[URI], 'getCustomerList')->name('customer-list-search');
                Route::get(Customer::SEARCH_WITHOUT_ALL_CUSTOMER[URI], 'getCustomerListWithoutAllCustomerName')->name('customer-list-without-all-customer');
            });
        });

        // CREATE route for customer
        Route::middleware('module:user_section,create')->group(function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::post(Customer::ADD[URI], 'add')->name('add');
            });
        });

        // UPDATE routes for customer
        Route::middleware('module:user_section,update')->group(function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::post(Customer::UPDATE[URI], 'updateStatus')->name('status-update');
            });
        });

        // DELETE routes for customer
        Route::middleware('module:user_section,delete')->group(function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::delete(Customer::DELETE[URI], 'delete')->name('delete');
            });
        });

        // Subgroup: Wallet
        Route::group(['prefix' => 'wallet', 'as' => 'wallet.'], function () {

            // READ Wallet
            Route::middleware('module:user_section,read')->group(function () {
                Route::controller(CustomerWalletController::class)->group(function () {
                    Route::get(CustomerWallet::REPORT[URI], 'index')->name('report');
                    Route::get(CustomerWallet::EXPORT[URI], 'export')->name('export');
                    Route::get(CustomerWallet::BONUS_SETUP[URI], 'getBonusSetupView')->name('bonus-setup');
                    Route::get(CustomerWallet::BONUS_SETUP_EDIT[URI] . '/{id}', 'getUpdateView')->name('bonus-setup-edit');
                });
            });

            // CREATE Wallet
            Route::middleware('module:user_section,create')->group(function () {
                Route::controller(CustomerWalletController::class)->group(function () {
                    Route::post(CustomerWallet::ADD[URI], 'addFund')->name('add-fund');
                    Route::post(CustomerWallet::BONUS_SETUP[URI], 'addBonusSetup');
                });
            });

            // UPDATE Wallet
            Route::middleware('module:user_section,update')->group(function () {
                Route::controller(CustomerWalletController::class)->group(function () {
                    Route::post(CustomerWallet::BONUS_SETUP_UPDATE[URI], 'update')->name('bonus-setup-update');
                    Route::post(CustomerWallet::BONUS_SETUP_STATUS[URI], 'updateStatus')->name('bonus-setup-status');
                });
            });

            // DELETE Wallet
            Route::middleware('module:user_section,delete')->group(function () {
                Route::controller(CustomerWalletController::class)->group(function () {
                    Route::delete(CustomerWallet::BONUS_SETUP_DELETE[URI], 'deleteBonus')->name('bonus-setup-delete');
                });
            });
        });

        // Subgroup: Loyalty
        Route::group(['prefix' => 'loyalty', 'as' => 'loyalty.'], function () {

            // READ Loyalty
            Route::middleware('module:user_section,read')->group(function () {
                Route::controller(CustomerLoyaltyController::class)->group(function () {
                    Route::get(Customer::LOYALTY_REPORT[URI], 'index')->name('report');
                    Route::get(Customer::LOYALTY_EXPORT[URI], 'exportList')->name('export');
                });
            });
        });
    });


    Route::group(['prefix' => 'report', 'as' => 'report.'], function () {
        // READ permission only
        Route::middleware('module:report,read')->group(function () {
            Route::controller(InhouseProductSaleController::class)->group(function () {
                Route::get(InhouseProductSale::VIEW[URI], 'index')->name('inhouse-product-sale');
                Route::get(InhouseProductSale::EXPORT_EXCEL[URI], 'exportExcel')->name('inhouse-product-sale-export-excel');
                Route::get(InhouseProductSale::EXPORT_PDF[URI], 'exportPdf')->name('inhouse-product-sale-export-pdf');
            });

            Route::controller(CrmDealSalesReportController::class)->group(function () {
                Route::get(CrmDealSalesReport::VIEW[URI], 'index')->name('crm-sales-performance');
                Route::get(CrmDealSalesReport::EXPORT_EXCEL[URI], 'exportExcel')->name('crm-sales-performance-export-excel');
                Route::get(CrmDealSalesReport::EXPORT_PDF[URI], 'exportPdf')->name('crm-sales-performance-export-pdf');
            });

            Route::controller(CrmAgentSalesMatrixReportController::class)->group(function () {
                Route::get(CrmAgentSalesMatrixReport::VIEW[URI], 'index')->name('crm-agent-sales-matrix');
                Route::get(CrmAgentSalesMatrixReport::EXPORT_EXCEL[URI], 'exportExcel')->name('crm-agent-sales-matrix-export-excel');
                Route::get(CrmAgentSalesMatrixReport::EXPORT_PDF[URI], 'exportPdf')->name('crm-agent-sales-matrix-export-pdf');
            });

            Route::controller(CrmEmployeeChannelAssignmentReportController::class)->group(function () {
                Route::get(CrmEmployeeChannelAssignmentReport::VIEW[URI], 'index')->name('crm-employee-channel-assignment');
                Route::get(CrmEmployeeChannelAssignmentReport::EXPORT_EXCEL[URI], 'exportExcel')->name('crm-employee-channel-assignment-export-excel');
                Route::get(CrmEmployeeChannelAssignmentReport::EXPORT_PDF[URI], 'exportPdf')->name('crm-employee-channel-assignment-export-pdf');
            });
        });
    });


    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::get('settings', [CustomerController::class, 'getCustomerSettingsView'])
            ->name('customer-settings')
            ->middleware('module:system_settings,read');

        Route::post('settings', [CustomerController::class, 'update'])
            ->middleware('module:system_settings,update');
    });

    /*BRANCH*/
    Route::group(['prefix' => 'branch', 'as' => 'branch.'], function () {
        // READ permissions routes (view, list, export, etc.)
        Route::get(Branch::LIST[URI], [BranchController::class, 'index'])->middleware('module:branch_management,branch_list')->name('branch-list');
        Route::get(Branch::ASSIGN_MANAGER[URI] . '/{branch_id}', [BranchController::class, 'assignManager'])->name('assign-manager');
        Route::get(Branch::UPDATE[URI] . '/{id}', [BranchController::class, 'getUpdateView'])->middleware('module:branch_management,branch_edit')->name('update');
        Route::get(Branch::EXPORT[URI], [BranchController::class, 'exportList'])->middleware('module:branch_management,branch_list')->name('export');
        Route::get(Branch::PRODUCT_LIST[URI] . '/{branch_id}', [BranchController::class, 'getProductListView'])->name('product-list');
        Route::get(Branch::VIEW[URI] . '/{id}/{tab?}', [BranchController::class, 'getView'])->middleware('module:branch_management,branch_view')->name('view');
        Route::get('getCitiesArea', [BranchController::class, 'fGetCitiesArea'])->name('getCitiesArea');
        Route::get(Branch::BRANCH_STOCK_LIST[URI], [BranchController::class, 'fGetBranchesStockList'])->middleware('module:branch_management,branch_stock_list')->name('branch-stock-list');
        // CREATE permissions routes
        Route::get(Branch::ADD[URI], [BranchController::class, 'getAddView'])->middleware('module:branch_management,add_branch')->name('add');
        Route::post(Branch::ADD[URI], [BranchController::class, 'add'])->middleware('module:branch_management,add_branch');
        Route::post(Branch::ADD_MANAGER[URI] . '/{branch_id}', [BranchController::class, 'addManager'])->name('add-manager');
        // UPDATE permissions routes
        Route::post(Branch::UPDATE[URI] . '/{id}', [BranchController::class, 'update'])->middleware('module:branch_management,branch_edit');
        Route::post(Branch::UPDATE_MANAGER[URI] . '/{branch_id}', [BranchController::class, 'updateManager'])->middleware('module:branch_management,product_list')->name('update-manager');
        Route::post(Branch::STATUS[URI], [BranchController::class, 'updateStatus'])->middleware('module:branch_management,branch_edit')->name('updateStatus');
        Route::post(Branch::UPDATE_SETTING[URI] . '/{id}', [BranchController::class, 'updateSetting'])->name('update-setting');
        // DELETE permission routes
        Route::delete(Branch::DELETE['URI'] . '/{id}', [BranchController::class, 'deleteBranch'])->middleware('module:branch_management,branch_delete')->name('chose.delete');
    });

    /*BRANCH*/

    Route::group(['prefix' => 'department', 'as' => 'department.'], function () {

        Route::middleware('module:crm_section,read')->group(function () {
            Route::controller(DepartmentController::class)->group(function () {
                Route::get(Department::LIST[URI], 'index')->name('list');
                Route::get(Department::USER_VIEW[URI] . '/{dept_id}', 'fViewBranchUsers')->name('users');
                Route::get(Department::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::get(Department::EXPORT[URI], 'exportList')->name('export');
                Route::get(Department::PRODUCT_LIST[URI] . '/{branch_id}', 'getProductListView')->name('product-list');

                Route::get(Branch::VIEW[URI] . '/{id}/{tab?}', 'getView')->name('view');
                Route::get('getCitiesArea', 'fGetCitiesArea')->name('getCitiesArea');
            });
        });

        // CREATE routes
        Route::middleware('module:crm_section,create')->group(function () {
            Route::controller(DepartmentController::class)->group(function () {
                Route::get(Department::ADD[URI], 'getAddView')->name('add');
                Route::post(Department::ADD[URI], 'add')->name('add');
                Route::get(Department::USER_ADD[URI] . '/{dept_id}', 'fAddBranchUsers')->name('add-users');
                Route::post(Department::USER_ADD[URI] . '/{dept_id}', 'addDepartmentUsers')->name('add-users');
            });
        });

        // UPDATE routes
        Route::middleware('module:crm_section,update')->group(function () {
            Route::controller(DepartmentController::class)->group(function () {
                Route::post(Department::UPDATE[URI] . '/{id}', 'update')->name('update');
                Route::post(Department::STATUS[URI], 'updateStatus')->name('updateStatus');
                Route::post(Branch::UPDATE_SETTING[URI] . '/{id}', 'updateSetting')->name('update-setting');
            });
        });

        // DELETE routes
        Route::middleware('module:crm_section,delete')->group(function () {
            Route::controller(DepartmentController::class)->group(function () {
                Route::post(Department::DELETE[URI], 'delete')->name('delete');
            });
        });
    });

    /*DEPARTMENT*/
    /*task-MGNT*/
    Route::group(['prefix' => 'task-management', 'as' => 'task-management.'], function () {

        // READ routes
        Route::middleware('module:task_section,read')->group(function () {
            Route::controller(TaskManagementController::class)->group(function () {
                Route::get(TaskManagement::INDEX[URI], 'index')->name('index');
                Route::get(TaskManagement::ADD[URI], 'getAddView')->name('add');  // Usually view for add form is read permission
            });
        });

        // CREATE routes
        Route::middleware('module:task_section,create')->group(function () {
            Route::controller(TaskManagementController::class)->group(function () {
                Route::post(TaskManagement::ADD[URI], 'add')->name('add');
            });
        });

        // DELETE routes
        Route::middleware('module:task_section,delete')->group(function () {
            Route::controller(TaskManagementController::class)->group(function () {
                Route::post(TaskManagement::DELETE[URI], 'delete')->name('delete');
            });
        });
    });

    /*task-MGNT*/

    Route::group(['prefix' => 'vendors', 'as' => 'vendors.'], function () {

        // READ permission group
        Route::middleware('module:user_section,read')->group(function () {
            Route::controller(VendorController::class)->group(function () {
                Route::get(Vendor::LIST[URI], 'index')->name('vendor-list');
                Route::get(Vendor::ADD[URI], 'getAddView')->name('add');  // view to add vendor
                Route::get(Vendor::ORDER_LIST[URI] . '/{vendor_id}', 'getOrderListView')->name('order-list');
                Route::get(Vendor::ORDER_LIST_EXPORT[URI] . '/{vendor_id}', 'exportOrderList')->name('order-list-export');
                Route::get(Vendor::EXPORT[URI], 'exportList')->name('export');
                Route::get(Vendor::PRODUCT_LIST[URI] . '/{vendor_id}', 'getProductListView')->name('product-list');
                Route::get(Vendor::ORDER_DETAILS[URI] . '/{order_id}/{vendor_id}', 'getOrderDetailsView')->name('order-details');
                Route::get(Vendor::VIEW[URI] . '/{id}/{tab?}', 'getView')->name('view');

                Route::get(Vendor::WITHDRAW_LIST[URI], 'getWithdrawListView')->name('withdraw_list');
                Route::get(Vendor::WITHDRAW_LIST_EXPORT[URI], 'exportWithdrawList')->name('withdraw-list-export-excel');
                Route::get(Vendor::WITHDRAW_VIEW[URI] . '/{withdrawId}/{vendorId}', 'getWithdrawView')->name('withdraw_view');
            });

            Route::group(['prefix' => 'withdraw-method', 'as' => 'withdraw-method.'], function () {
                Route::controller(WithdrawalMethodController::class)->group(function () {
                    Route::get(WithdrawalMethod::LIST[URI], 'index')->name('list');
                    Route::get(WithdrawalMethod::ADD[URI], 'getAddView')->name('add');
                    Route::get(WithdrawalMethod::UPDATE[URI] . '/{id}', 'getUpdateView')->name('edit');
                });
            });
        });

        // CREATE permission group
        Route::middleware('module:user_section,create')->group(function () {
            Route::controller(VendorController::class)->group(function () {
                Route::post(Vendor::ADD[URI], 'add');
            });

            Route::group(['prefix' => 'withdraw-method', 'as' => 'withdraw-method.'], function () {
                Route::controller(WithdrawalMethodController::class)->group(function () {
                    Route::post(WithdrawalMethod::ADD[URI], 'add');
                });
            });
        });

        // UPDATE permission group
        Route::middleware('module:user_section,update')->group(function () {
            Route::controller(VendorController::class)->group(function () {
                Route::post(Vendor::STATUS[URI], 'updateStatus')->name('updateStatus');
                Route::post(Vendor::UPDATE_SETTING[URI] . '/{id}', 'updateSetting')->name('update-setting');
                Route::post(Vendor::SALES_COMMISSION_UPDATE[URI] . '/{id}', 'updateSalesCommission')->name('sales-commission-update');
            });

            Route::group(['prefix' => 'withdraw-method', 'as' => 'withdraw-method.'], function () {
                Route::controller(WithdrawalMethodController::class)->group(function () {
                    Route::post(WithdrawalMethod::DEFAULT_STATUS[URI], 'updateDefaultStatus')->name('default-status');
                    Route::post(WithdrawalMethod::STATUS[URI], 'updateStatus')->name('status-update');
                    Route::post(WithdrawalMethod::UPDATE[URI], 'update')->name('update');
                });
            });
        });

        // DELETE permission group
        Route::middleware('module:user_section,delete')->group(function () {
            Route::controller(VendorController::class)->group(function () {});

            Route::group(['prefix' => 'withdraw-method', 'as' => 'withdraw-method.'], function () {
                Route::controller(WithdrawalMethodController::class)->group(function () {
                    Route::delete(WithdrawalMethod::DELETE[URI] . '/{id}', 'delete')->name('delete');
                });
            });
        });
    });


    Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {

        // READ permissions
        Route::middleware('module:employee_management,read')->group(function () {
            Route::controller(EmployeeController::class)->group(function () {
                Route::get(Employee::LIST[URI], 'index')->name('list');
                Route::get(Employee::ADD[URI], 'getAddView')->name('add-new');  // form to add
                Route::get(Employee::EXPORT[URI], 'exportList')->name('export');
                Route::get(Employee::VIEW[URI] . '/{id}', 'getView')->name('view');
                Route::get(Employee::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            });
        });

        // CREATE permissions
        Route::middleware('module:employee_management,create')->group(function () {
            Route::controller(EmployeeController::class)->group(function () {
                Route::post(Employee::ADD[URI], 'add')->name('add-new-post');
            });
        });

        // UPDATE permissions
        Route::middleware('module:employee_management,update')->group(function () {
            Route::controller(EmployeeController::class)->group(function () {
                Route::post(Employee::UPDATE[URI] . '/{id}', 'update');
                Route::post(Employee::STATUS[URI], 'updateStatus')->name('status');
                Route::post(Employee::UPDATE_BRANCH[URI], 'updateEmployeeBranch')->name('update-employee-branch');
                Route::post(Employee::UPDATE_DEPARTMENT[URI], 'updateEmployeeDepartment')->name('update-employee-department');
            });
        });
    });


    Route::group(['prefix' => 'custom-role', 'as' => 'custom-role.'], function () {

        // READ permissions
        Route::middleware('module:user_section,read')->group(function () {
            Route::controller(CustomRoleController::class)->group(function () {
                Route::get(CustomRole::ADD[URI], 'index')->name('create');  // Usually the form to create role
                Route::get(CustomRole::VIEW[URI], 'viewRole')->name('view-all');  // Usually the form to create role
                Route::get(CustomRole::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::get(CustomRole::EXPORT[URI], 'exportList')->name('export');
            });
        });

        // CREATE permissions
        Route::middleware('module:user_section,create')->group(function () {
            Route::controller(CustomRoleController::class)->group(function () {
                Route::post(CustomRole::ADD[URI], 'add')->name('store');
            });
        });

        // UPDATE permissions
        Route::middleware('module:user_section,update')->group(function () {
            Route::controller(CustomRoleController::class)->group(function () {
                Route::post(CustomRole::UPDATE[URI] . '/{id}', 'update');
                Route::post(CustomRole::STATUS[URI], 'updateStatus')->name('employee-role-status');
            });
        });

        // DELETE permissions
        Route::middleware('module:user_section,delete')->group(function () {
            Route::controller(CustomRoleController::class)->group(function () {
                Route::post(CustomRole::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    /*  report */
    Route::group(['prefix' => 'report', 'as' => 'report.'], function () {

        Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {

            // READ permissions (for listing and viewing)
            Route::middleware('module:report,read')->group(function () {
                Route::controller(RefundTransactionController::class)->group(function () {
                    Route::get(RefundTransaction::INDEX[URI], 'index')->name('refund-transaction-list');
                    Route::get(RefundTransaction::GENERATE_PDF[URI], 'getRefundTransactionPDF')->name('refund-transaction-summary-pdf');
                });
            });

            // EXPORT permission (usually under read or export permission)
            Route::middleware('module:report,export')->group(function () {
                Route::controller(RefundTransactionController::class)->group(function () {
                    Route::get(RefundTransaction::EXPORT[URI], 'exportRefundTransaction')->name('refund-transaction-export');
                });
            });
        });
    });


    Route::group(['prefix' => 'report', 'as' => 'report.'], function () {

        // Earning reports (read, export)
        Route::middleware('module:report,read')->group(function () {
            Route::controller(ReportController::class)->group(function () {
                Route::get('earning', 'earning_index')->name('earning');
                Route::get('admin-earning', 'admin_earning')->name('admin-earning');
                Route::post('admin-earning-duration-download-pdf', 'admin_earning_duration_download_pdf')->name('admin-earning-duration-download-pdf');
                Route::get('vendor-earning', 'vendorEarning')->name('vendor-earning');
                Route::any('set-date', 'set_date')->name('set-date');
            });

            Route::controller(OrderReportController::class)->group(function () {
                Route::get('order', 'order_list')->name('order');
            });

            Route::controller(ProductReportController::class)->group(function () {
                Route::get('all-product', 'all_product')->name('all-product');
            });

            Route::controller(VendorProductSaleReportController::class)->group(function () {
                Route::get('vendor-report', 'vendorReport')->name('vendor-report');
            });
        });

        // Export reports (export)
        Route::middleware('module:report,export')->group(function () {
            Route::controller(ReportController::class)->group(function () {
                Route::get('admin-earning-excel-export', 'exportAdminEarning')->name('admin-earning-excel-export');
                Route::get('vendor-earning-excel-export', 'exportVendorEarning')->name('vendor-earning-excel-export');
            });

            Route::controller(OrderReportController::class)->group(function () {
                Route::get('order-report-excel', 'orderReportExportExcel')->name('order-report-excel');
                Route::get('order-report-pdf', 'exportOrderReportInPDF')->name('order-report-pdf');
            });

            Route::controller(ProductReportController::class)->group(function () {
                Route::get('all-product-excel', 'allProductExportExcel')->name('all-product-excel');
            });

            Route::controller(VendorProductSaleReportController::class)->group(function () {
                Route::get('vendor-report-export', 'exportVendorReport')->name('vendor-report-export');
            });
        });
    });


    Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {

        // Routes for viewing (read)
        Route::middleware('module:report,read')->group(function () {
            Route::controller(TransactionReportController::class)->group(function () {
                Route::get('order-transaction-list', 'order_transaction_list')->name('order-transaction-list');
                Route::get('pdf-order-wise-transaction', 'pdf_order_wise_transaction')->name('pdf-order-wise-transaction');
                Route::get('order-transaction-summary-pdf', 'order_transaction_summary_pdf')->name('order-transaction-summary-pdf');
                Route::get('expense-transaction-list', 'expense_transaction_list')->name('expense-transaction-list');
                Route::get('pdf-order-wise-expense-transaction', 'pdf_order_wise_expense_transaction')->name('pdf-order-wise-expense-transaction');
                Route::get('expense-transaction-summary-pdf', 'expense_transaction_summary_pdf')->name('expense-transaction-summary-pdf');
                Route::get('wallet-bonus', 'wallet_bonus')->name('wallet-bonus');
            });
        });

        // Routes for export
        Route::middleware('module:report,export')->group(function () {
            Route::controller(TransactionReportController::class)->group(function () {
                Route::get('order-transaction-export-excel', 'orderTransactionExportExcel')->name('order-transaction-export-excel');
                Route::get('expense-transaction-export-excel', 'expenseTransactionExportExcel')->name('expense-transaction-export-excel');
            });
        });
    });


    Route::group(['prefix' => 'stock', 'as' => 'stock.'], function () {

        // Routes for viewing and filtering (read)
        Route::middleware('module:report,read')->group(function () {
            Route::controller(ProductStockReportController::class)->group(function () {
                Route::get('product-stock', 'index')->name('product-stock');
                Route::post('ps-filter', 'filter')->name('ps-filter');
            });

            Route::controller(ProductWishlistReportController::class)->group(function () {
                Route::get('product-in-wishlist', 'index')->name('product-in-wishlist');
            });
        });

        // Routes for export
        Route::middleware('module:report,export')->group(function () {
            Route::controller(ProductStockReportController::class)->group(function () {
                Route::get('product-stock-export', 'export')->name('product-stock-export');
                Route::get('product-stock-export-pdf', 'exportPdf')->name('product-stock-export-pdf');
            });

            Route::controller(ProductWishlistReportController::class)->group(function () {
                Route::get('wishlist-product-export', 'export')->name('wishlist-product-export');
            });
        });
    });


    // Reviews
    Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {

        // Routes for viewing, searching, and replying (read / moderate)
        Route::middleware('module:user_section,read')->group(function () {
            Route::controller(ReviewController::class)->group(function () {
                Route::get(Review::LIST[URI], 'index')->name('list');
                Route::get(Review::STATUS[URI], 'updateStatus')->name('status');
                Route::get(Review::SEARCH[URI], 'getCustomerList')->name('customer-list-search');
                Route::any(Review::SEARCH_PRODUCT[URI], 'search')->name('search-product');
                Route::post(Review::REVIEW_REPLY[URI], 'addReviewReply')->name('add-review-reply');
            });
        });

        // Routes for export
        Route::middleware('module:user_section,export')->group(function () {
            Route::controller(ReviewController::class)->group(function () {
                Route::get(Review::EXPORT[URI], 'exportList')->name('export');
            });
        });
    });


    // Coupon
    Route::group(['prefix' => 'coupon', 'as' => 'coupon.'], function () {
        // Read routes (list, quick view, status update, vendor list)
        Route::middleware('module:promotion_management,coupon')->group(function () {
            Route::controller(CouponController::class)->group(function () {
                Route::get(Coupon::ADD[URI], 'getAddListView')->name('add'); // typically read to show add form
                Route::get(Coupon::QUICK_VIEW[URI], 'quickView')->name('quick-view-details');
                Route::get(Coupon::STATUS[URI] . '/{id}/{status}', 'updateStatus')->name('status');
                Route::post(Coupon::VENDOR_LIST[URI], 'getVendorList')->name('ajax-get-vendor');
            });
        });
        // Create routes
        Route::middleware('module:promotion_management,coupon')->group(function () {
            Route::controller(CouponController::class)->group(function () {
                Route::post(Coupon::ADD[URI], 'add');
            });
        });

        // Update routes
        Route::middleware('module:promotion_management,coupon')->group(function () {
            Route::controller(CouponController::class)->group(function () {
                Route::get(Coupon::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::post(Coupon::UPDATE[URI] . '/{id}', 'update');
            });
        });

        // Delete routes
        Route::middleware('module:promotion_management,coupon')->group(function () {
            Route::controller(CouponController::class)->group(function () {
                Route::delete(Coupon::DELETE[URI] . '/{id}', 'delete')->name('delete');
            });
        });

        // Export routes
        Route::middleware('module:promotion_management,coupon')->group(function () {
            Route::controller(CouponController::class)->group(function () {
                Route::get(Coupon::EXPORT[URI], 'exportList')->name('export');
            });
        });
    });


    Route::group(['prefix' => 'deal', 'as' => 'deal.', 'middleware' => ['module:promotion_management']], function () {

        // --------------------
        // FlashDeal routes
        // --------------------

        // Read routes (list, get update view, add product view, search)
        Route::middleware('module:promotion_management,flash_deals')->group(function () {
            Route::controller(FlashDealController::class)->group(function () {
                Route::get(FlashDeal::LIST[URI], 'index')->name('flash');
                Route::get(FlashDeal::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::get(FlashDeal::ADD_PRODUCT[URI] . '/{deal_id}', 'getAddProductView')->name('add-product');
                Route::any(FlashDeal::SEARCH[URI], 'search')->name('search-product');
            });
        });

        // Create routes
        Route::middleware('module:promotion_management,flash_deals')->group(function () {
            Route::controller(FlashDealController::class)->group(function () {
                Route::post(FlashDeal::LIST[URI], 'add');
                Route::post(FlashDeal::ADD_PRODUCT[URI] . '/{deal_id}', 'addProduct');
            });
        });

        // Update routes
        Route::middleware('module:promotion_management,flash_deals')->group(function () {
            Route::controller(FlashDealController::class)->group(function () {
                Route::post(FlashDeal::UPDATE[URI] . '/{id}', 'update')->name('update-data');
                Route::post(FlashDeal::STATUS[URI], 'updateStatus')->name('status-update');
            });
        });

        // Delete routes
        Route::middleware('module:promotion_management,flash_deals')->group(function () {
            Route::controller(FlashDealController::class)->group(function () {
                Route::post(FlashDeal::DELETE[URI], 'delete')->name('delete-product');
            });
        });


        // --------------------
        // DealOfTheDay routes
        // --------------------

        // Read
        Route::middleware('module:promotion_management,deal_of_the_day')->group(function () {
            Route::controller(DealOfTheDayController::class)->group(function () {
                Route::get(DealOfTheDay::LIST[URI], 'index')->name('day');
                Route::get(DealOfTheDay::UPDATE[URI] . '/{id}', 'getUpdateView')->name('day-update');
            });
        });

        // Create
        Route::middleware('module:promotion_management,deal_of_the_day')->group(function () {
            Route::controller(DealOfTheDayController::class)->group(function () {
                Route::post(DealOfTheDay::LIST[URI], 'add');
            });
        });

        // Update
        Route::middleware('module:promotion_management,deal_of_the_day')->group(function () {
            Route::controller(DealOfTheDayController::class)->group(function () {
                Route::post(DealOfTheDay::UPDATE[URI] . '/{id}', 'update');
                Route::post(DealOfTheDay::STATUS[URI], 'updateStatus')->name('day-status-update');
            });
        });

        // Delete
        Route::middleware('module:promotion_management,deal_of_the_day')->group(function () {
            Route::controller(DealOfTheDayController::class)->group(function () {
                Route::post(DealOfTheDay::DELETE[URI], 'delete')->name('day-delete');
            });
        });


        // --------------------
        // FeaturedDeal routes
        // --------------------

        // Read
        Route::middleware('module:promotion_management,featured_deal')->group(function () {
            Route::controller(FeaturedDealController::class)->group(function () {
                Route::get(FeatureDeal::LIST[URI], 'index')->name('feature');
                Route::get(FeatureDeal::UPDATE[URI] . '/{id}', 'getUpdateView')->name('edit');
            });
        });

        // Update
        Route::middleware('module:promotion_management,featured_deal')->group(function () {
            Route::controller(FeaturedDealController::class)->group(function () {
                Route::post(FeatureDeal::UPDATE[URI], 'update')->name('featured-update');
                Route::post(FeatureDeal::STATUS[URI], 'updateStatus')->name('feature-status');
            });
        });


        // --------------------
        // ClearanceSale routes
        // --------------------

        // Read
        Route::group(['prefix' => 'clearance-sale', 'as' => 'clearance-sale.'], function () {

            Route::middleware('module:promotion_management,clearance_sale')->group(function () {

                Route::controller(ClearanceSaleController::class)->group(function () {
                    Route::get(ClearanceSale::LIST[URI], 'index')->name('index');
                    Route::get(ClearanceSale::SEARCH[URI], 'getSearchedProductsView')->name('search-product-for-clearance');
                    Route::get(ClearanceSale::MULTIPLE_PRODUCT_DETAILS[URI], 'getMultipleProductDetailsView')->name('multiple-clearance-product-details');
                });

                Route::controller(ClearanceSaleVendorOfferController::class)->group(function () {
                    Route::get(ClearanceSale::VENDOR_OFFERS[URI], 'index')->name('vendor-offers');
                    Route::get(ClearanceSale::VENDOR_SEARCH[URI], 'getSearchedVendorsView')->name('search-vendor-for-clearance');
                });

                Route::controller(ClearanceSalePrioritySetupController::class)->group(function () {
                    Route::get(ClearanceSale::PRIORITY_SETUP[URI], 'index')->name('priority-setup');
                });
            });

            // Create
            Route::middleware('module:promotion_management,clearance_sale')->group(function () {

                Route::controller(ClearanceSaleController::class)->group(function () {
                    Route::post(ClearanceSale::ADD_PRODUCT[URI], 'addClearanceProduct')->name('add-product');
                });

                Route::controller(ClearanceSaleVendorOfferController::class)->group(function () {
                    Route::post(ClearanceSale::ADD_VENDOR[URI], 'addClearanceVendorProduct')->name('vendor-add');
                });
            });

            // Update
            Route::middleware('module:promotion_management,clearance_sale')->group(function () {
                Route::controller(ClearanceSaleController::class)->group(function () {
                    Route::post(ClearanceSale::STATUS[URI], 'updateStatus')->name('status-update');
                    Route::post(ClearanceSale::UPDATE_CONFIG[URI], 'updateClearanceConfig')->name('update-config');
                    Route::post(ClearanceSale::PRODUCT_STATUS[URI], 'updateProductStatus')->name('product-status-update');
                    Route::post(ClearanceSale::UPDATE_DISCOUNT[URI], 'updateDiscountAmount')->name('update-discount');
                });

                Route::controller(ClearanceSaleVendorOfferController::class)->group(function () {
                    Route::post(ClearanceSale::UPDATE_STATUS[URI], 'updateVendorStatus')->name('update-vendor-status');
                    Route::post(ClearanceSale::UPDATE_OFFER_STATUS[URI], 'updateVendorOfferStatus')->name('update-vendor-offer-status');
                });

                Route::controller(ClearanceSalePrioritySetupController::class)->group(function () {
                    Route::post(ClearanceSale::PRIORITY_CONFIG[URI], 'updateConfig')->name('priority-setup-config');
                });
            });

            // Delete
            Route::middleware('module:promotion_management,clearance_sale')->group(function () {

                Route::controller(ClearanceSaleController::class)->group(function () {
                    Route::delete(ClearanceSale::CLEARANCE_DELETE[URI] . '/{product_id}', 'deleteClearanceProduct')->name('clearance-delete');
                    Route::delete(ClearanceSale::CLEARANCE_PRODUCTS_DELETE[URI], 'deleteClearanceAllProduct')->name('clearance-delete-all-product');
                });

                Route::controller(ClearanceSaleVendorOfferController::class)->group(function () {
                    Route::delete(ClearanceSale::VENDOR_DELETE[URI] . '/{id}', 'deleteVendorOffer')->name('vendor-delete');
                });
            });
        });
    });


    /** Notification and push notification */
    // Push Notification Settings Routes
    Route::group(['prefix' => 'push-notification', 'as' => 'push-notification.', 'middleware' => ['module:promotion_management']], function () {

        // Read permissions
        Route::middleware('module:promotion_management,push_notification_setup')->group(function () {
            Route::controller(PushNotificationSettingsController::class)->group(function () {
                Route::get(PushNotification::INDEX[URI], 'index')->name('index');
                Route::get(PushNotification::FIREBASE_CONFIGURATION[URI], 'getFirebaseConfigurationView')->name('firebase-configuration');
            });
        });

        // Update permissions
        Route::middleware('module:promotion_management,push_notification_setup')->group(function () {
            Route::controller(PushNotificationSettingsController::class)->group(function () {
                Route::post(PushNotification::UPDATE[URI], 'updatePushNotificationMessage')->name('update');
                Route::post(PushNotification::FIREBASE_CONFIGURATION[URI], 'getFirebaseConfigurationUpdate')->name('update-firebase-configuration');
            });
        });
    });


    // Notification Routes
    Route::group(['prefix' => 'notification', 'as' => 'notification.', 'middleware' => ['module:promotion_management']], function () {

        // Read permissions
        Route::middleware('module:promotion_management,send_notifications')->group(function () {
            Route::controller(NotificationController::class)->group(function () {
                Route::get(Notification::INDEX[URI], 'index')->name('index');
                Route::get(Notification::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            });
        });

        // Create permissions
        Route::middleware('module:promotion_management,send_notifications')->group(function () {
            Route::controller(NotificationController::class)->group(function () {
                Route::post(Notification::INDEX[URI], 'add');
            });
        });

        // Update permissions
        Route::middleware('module:promotion_management,send_notifications')->group(function () {
            Route::controller(NotificationController::class)->group(function () {
                Route::post(Notification::UPDATE[URI] . '/{id}', 'update');
                Route::post(Notification::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
                Route::post(Notification::RESEND_NOTIFICATION[URI], 'resendNotification')->name('resend-notification');
            });
        });

        // Delete permissions
        Route::middleware('module:promotion_management,send_notifications')->group(function () {
            Route::controller(NotificationController::class)->group(function () {
                Route::post(Notification::DELETE[URI], 'delete')->name('delete');
            });
        });
    });

    Route::group(['prefix' => 'notification-setup', 'as' => 'notification-setup.', 'middleware' => ['module:promotion_management']], function () {
        Route::middleware('module:promotion_management,push_notification_setup')->group(function () {
            Route::controller(NotificationSetupController::class)->group(function () {
                Route::get(NotificationSetup::INDEX[URI] . '/{type}', 'index')->name('index');
            });
        });
    });

    Route::group(['prefix' => 'support-ticket', 'as' => 'support-ticket.', 'middleware' => ['module:crm_section']], function () {
        Route::middleware('module:crm_section')->group(function () {
            Route::controller(SupportTicketController::class)->group(function () {
                Route::get(SupportTicket::LIST[URI] . '/{status}', 'index')->name('view');
                Route::get(SupportTicket::VIEW[URI] . '/{id}', 'getView')->name('singleTicket')->middleware('module:crm_section,ticket_conversation');
                Route::get(SupportTicket::DETAILS[URI] . '/{id}', 'getDetailsView')->name('details');
                Route::get(SupportTicket::EXPORT[URI], 'export')->name('export');
            });
        });
        Route::middleware('module:crm_section,update')->group(function () {
            Route::controller(SupportTicketController::class)->group(function () {
                Route::post(SupportTicket::STATUS[URI], 'updateStatus')->name('status');
                Route::post(SupportTicket::VIEW[URI] . '/{id}', 'reply')->name('replay');
                Route::post(SupportTicket::ESCLATE_RETAIL[URI], 'escalateRetail')->name('esclate.retail');
                Route::post(SupportTicket::ESCLATE_WHOLESALE[URI], 'escalateWholesale')->name('esclate.wholesale');
            });
        });

        Route::middleware('module:crm_section,update')->group(function () {
            Route::post('escalate', [SupportTicketController::class, 'escalate'])->name('escalate');
        });
        Route::group(['prefix' => 'service', 'as' => 'service.'], function () {
            Route::middleware('module:crm_section')->group(function () {
                Route::controller(ServiceTicketController::class)->group(function () {
                    Route::post('assign', 'assignTicket')->name('assign')->middleware('module:crm_section,service_ticket_assign');
                    Route::get('service/{id}', 'getDetails')->name('singleTicket')->middleware('module:crm_section,service_ticket_details');
                    Route::post('estimate', 'createEstimate')->name('estimate')->middleware('module:crm_section,service_ticket_create_estimate');
                    Route::post('schedule', 'scheduleTicket')->name('schedule')->middleware('module:crm_section,service_ticket_schedule');
                    Route::post('start-job', 'startJob')->name('start-job')->middleware('module:crm_section,service_ticket_start_job');
                    Route::post('complete-job', 'completeJob')->name('complete-job')->middleware('module:crm_section,service_ticket_complete_job');
                    Route::post('change-order', 'createChangeOrder')->name('change-order')->middleware('module:crm_section,service_ticket_change_order');
                    Route::post('qa', 'qaConfirmation')->name('qa')->middleware('module:crm_section,service_ticket_qa');
                    Route::post('close', 'closeTicket')->name('close')->middleware('module:crm_section,service_ticket_close');
                    Route::post('cancel', 'cancelTicket')->name('cancel')->middleware('module:crm_section,service_ticket_cancel');
                    Route::post('escalate', 'escalate')->name('escalate');
                });
            });
        });

        Route::group(['prefix' => 'career', 'as' => 'career.'], function () {
            Route::middleware('module:crm_section')->group(function () {
                Route::controller(CareerTicketController::class)->group(function () {
                    Route::get('/', 'index')->name('index')->middleware('module:crm_section,career_ticket_list');
                    Route::get('/{id}', 'getDetails')->name('single')->middleware('module:crm_section,career_ticket_view');
                    Route::post('/status', 'updateStatus')->name('status')->middleware('module:crm_section,career_ticket_update_status');
                    Route::post('/assign', 'assignRecruiter')->name('assign')->middleware('module:crm_section,career_ticket_assign_recruiter');
                    Route::post('/screen', 'logScreening')->name('screen')->middleware('module:crm_section,career_ticket_screen');
                    Route::post('/schedule-interview', 'scheduleInterview')->name('schedule-interview')->middleware('module:crm_section,career_ticket_schedule_interview');
                    Route::post('/conduct-interview', 'conductInterview')->name('conduct-interview')->middleware('module:crm_section,career_ticket_conduct_interview');
                    Route::post('/attach-offer', 'attachSignedOffer')->name('attach-offer')->middleware('module:crm_section,career_ticket_attach_offer');
                    Route::post('/decline-offer', 'recordDeclinedOffer')->name('decline-offer')->middleware('module:crm_section,career_ticket_decline_offer');
                    Route::post('/reject', 'rejectCandidate')->name('reject')->middleware('module:crm_section,career_ticket_reject');
                    Route::post('/talent-pool', 'addToTalentPool')->name('talent-pool')->middleware('module:crm_section,career_ticket_add_to_talent_pool');
                    Route::get('/pool/export', 'export')->name('pool.export')->middleware('module:crm_section,career_ticket_export_pool');
                    Route::post('/reply', 'reply')->name('reply')->middleware('module:crm_section,career_ticket_reply');
                    Route::post('/escalate', 'escalate')->name('escalate');
                });
            });
        });
    });

    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::controller(TaskNotificationsController::class)->group(function () {
            Route::get(Notifications::LIST[URI], 'list')->name('list');
            Route::get(Notifications::VIEW[URI] . '/{id}', 'view')->name('view');
            Route::get(Notifications::TICKET_VIEW[URI] . '/{id}', 'getConversationReview')->name('ticket');
        });
    });


    Route::group(['prefix' => 'complaints', 'as' => 'complaints.', 'middleware' => ['module:crm_section']], function () {
        Route::middleware('module:crm_section')->group(function () {
            Route::controller(ComplaintController::class)->group(function () {
                Route::get(Complaint::INDEX[URI], 'index')->name('index');
                Route::get(Complaint::VIEW[URI] . '/{id}', 'getView')->name('singleTicket');
                Route::get(Complaint::DEPARTMENT[URI], 'getDepartments')->name('get-departments');
                Route::get(Complaint::DEPARTMENT_EMPLOYEE[URI], 'getDepartmentEmployee')->name('get-department-employee');
                Route::post(Complaint::DASHBOARD[URI],  'dasboard')->name('dashboard');
            });
        });

        Route::middleware('module:crm_section')->group(function () {
            Route::controller(ComplaintController::class)->group(function () {
                Route::post(Complaint::STATUS[URI], 'updateStatus')->name('status');
                Route::post(Complaint::VIEW[URI] . '/{id}', 'reply')->name('replay');
                Route::post(Complaint::TICKET_DEPARTMENT[URI], 'updateTicketDepartment')->name('update-ticket-department')->middleware('module:crm_section,ticket_department_update');
                Route::post(Complaint::TICKET_EMPLOYEE[URI], 'updateTicketEmploayee')->name('update-ticket-employee')->middleware('module:crm_section,ticket_employee_update');
                Route::post(Complaint::TICKET_FOLLOW_UP[URI], 'updateTicketFollowUp')->name('update-ticket-follow-up')->middleware('module:crm_section,update_ticket_follow_up');
                Route::post(Complaint::SUPPORT_FOLLOW_UP[URI], 'updateSupportTicketFollowUp')->name('update-support-follow-up')->middleware('module:crm_section,ticket_support_follow_up');
                Route::post(Complaint::COMPLAIN_FOLLOW_UP[URI], 'updateComplainTicketFollowUp')->name('update-complain-follow-up')->middleware('module:crm_section,ticket_complain_follow_up');
                Route::post(Complaint::WHOLESALE_FOLLOW_UP[URI], 'updateWholesaleFollowUp')->name('update-wholesale-follow-up')->middleware('module:crm_section,ticket_wholesale_follow_up');
            });
        });
    });

    Route::group(['prefix' => 'complaints', 'as' => 'complaints.', 'middleware' => ['module:crm_section']], function () {
        Route::middleware('module:crm_section,update')->group(function () {
            Route::post('escalate', [ComplaintController::class, 'escalate'])->name('escalate');
        });
    });
    Route::group(['prefix' => 'sla', 'as' => 'sla.', 'middleware' => ['auth:admin']], function () {
        Route::controller(SlaController::class)->group(function () {
            Route::get('/',  'index')->name('index');
            Route::get('/create',  'create')->name('create');
            Route::post('/store',  'store')->name('store');
            Route::post('/status',  'status')->name('status');
            Route::get('/{id}/edit',  'edit')->name('edit');
            Route::put('/{id}/update',  'update')->name('update');
            Route::delete('/{id}',  'destroy')->name('destroy');
        });
    });
    Route::group(['prefix' => 'crm', 'as' => 'crm.'], function () {
        Route::controller(InboxMessageController::class)->group(function () {
            Route::get(Crm::INDEX[URI], 'index')->name('index');
            Route::get(Crm::EXPORT[URI], 'exportList')->name('messages.export');
            Route::get(Crm::SHOW[URI] . '/{id}', 'showMassage')->name('massage.show');
            Route::post(Crm::ADD_NEW_MASSAGE[URI], 'storeNewMassage')->name('add.massage');
            Route::post(Crm::TICKET_DEPARTMENT[URI], 'updateTicketDepartment')->name('update-ticket-department');
            Route::post(Crm::CONVERT_INQUIRY[URI], 'convertInquiry')->name('convert-inquiry');
            Route::post(Crm::CONVERT_BULK_INQUIRY[URI], 'convertBulkInquiry')->name('convert-bulk-inquiry');
            Route::post(Crm::TYPE_CHANGE[URI], 'updateMessageType')->name('update-massage-type');
            Route::post(Crm::MASSAGE_IGNORE[URI], 'ignoreMessage')->name('ignore');
            Route::post(Crm::SPAM_MASSAGE[URI], 'spamMessage')->name('mark-spam');
            Route::post(Crm::ASSIGN_EMPLOYEE[URI], 'assignEmployee')->name('employee-assign');
            Route::post(Crm::ASSIGN_OWNER[URI], 'assignOwner')->name('owner-assign');
            Route::delete(Crm::MASSAGE_DELETE[URI] . '/{id}', 'destroy')->name('messages.destroy');
            Route::get(Crm::DEPARTMENT_EMPLOYEE[URI], 'getEmployeesByDepartment')->name('getemployee');
            Route::get('inbox/user-info/{id}',  'getUserInfo')->name('inbox.user-info');
            Route::post('inbox/connect-user',  'connectUser')->name('inbox.connect-user');
        });

        //CRM Chart Routes
        Route::get('message-stats', [DashboardChartController::class, 'messageStats'])->name('message.stats');
        Route::get('dashboard-stats', [DashboardChartController::class, 'getDashboardStats'])->name('dashboard.stats');
        Route::get('chart-view', [DashboardChartController::class, 'chartView'])->name('chart.view');
        Route::get('chart-data', [DashboardChartController::class, 'getChartData'])->name('chart.data');
        Route::get('export-excel', [DashboardChartController::class, 'exportExcel'])->name('export.excel');
        Route::get('export-pdf', [DashboardChartController::class, 'exportPdf'])->name('export.pdf');

        Route::middleware('module:crm_section,read')->group(function () {
            Route::controller(CrmDashboardController::class)->group(function () {
                Route::match(['get', 'post'], 'dashboard', 'index')->name('dashboard');
            });
        });

        Route::group(['prefix' => 'calendar', 'as' => 'calendar.', 'middleware' => ['module:crm_section']], function () {
            Route::get('/index', [CalendarController::class, 'index'])->name('index');
            Route::get('/events', [CalendarController::class, 'events'])->name('events');
            Route::post('/todo/add', [CalendarController::class, 'addTodo'])->name('todo.add');
        });


        Route::group(['prefix' => 'lead', 'as' => 'lead.'], function () {
            Route::middleware('module:crm_section')->group(function () {
                Route::controller(LeadController::class)->group(function () {

                    Route::get(Leads::INDEX[URI], 'index')
                        ->name('index')
                        ->middleware('module:crm_section,lead_list');

                    Route::get(Leads::SHOW[URI] . '/{id}', 'showLead')
                        ->name('show')
                        ->middleware('module:crm_section,lead_show');

                    Route::get(Leads::VIEW[URI] . '/{id}', 'view')
                        ->name('view')
                        ->middleware('module:crm_section,lead_view');

                    Route::get(Leads::SEARCH_PARTY[URI], 'searchParty')
                        ->name('searchParty');

                    Route::post(Leads::CONVERT_TO_DEAL[URI], 'convertToDeal')
                        ->name('convert-to-deal')
                        ->middleware('module:crm_section,lead_convert_to_deal');

                    Route::post(Leads::DESQUALIFY[URI], 'disqualify')
                        ->name('disqualify')
                        ->middleware('module:crm_section,lead_disqualify');

                    Route::get(Leads::DEPARTMENT_EMPLOYEE[URI], 'getEmployeesByDepartment')
                        ->name('getemployee');

                    Route::post(Leads::ASSIGN_EMPLOYEE[URI], 'assignEmployee')
                        ->name('employee-assign')
                        ->middleware('module:crm_section,lead_assign_employee');

                    Route::post(Leads::ASSIGN_OWNER[URI], 'assignOwner')
                        ->name('owner-assign')
                        ->middleware('module:crm_section,lead_assign_owner');

                    Route::post(Leads::ASSIGN_DEPARTMENT[URI], 'updateTicketDepartment')
                        ->name('update-ticket-department')
                        ->middleware('module:crm_section,lead_assign_department');

                    Route::get(Leads::EXPORT[URI], 'exportList')
                        ->name('export')
                        ->middleware('module:crm_section,lead_export');

                    Route::get(Leads::GET_USER[URI], 'getUserOrders')
                        ->name('user-orders')
                        ->middleware('module:crm_section,lead_get_user_orders');
                });
            });
        });


        Route::group(['prefix' => 'lead', 'middleware' => ['auth:admin', 'module:crm_section,update'], 'as' => 'lead.'], function () {
            Route::post('{id}/activity', [LeadController::class, 'storeActivity'])->name('activity.store');
            Route::post('{id}/note', [LeadController::class, 'storeNote'])->name('note.store');
            Route::post('{id}/task', [LeadController::class, 'storeTask'])->name('task.store');
            Route::post('{id}/call', [LeadController::class, 'storeCall'])->name('call.store');
            Route::post('{id}/file', [LeadController::class, 'storeFile'])->name('file.store');
            Route::put('{id}/task/{task_id}', [LeadController::class, 'updateTask'])->name('task.update');
            Route::post('{id}/task/{task_id}/complete', [LeadController::class, 'completeTask'])->name('task.complete');
            Route::post('escalate', [LeadController::class, 'escalate'])->name('escalate');
        });
        Route::group(['prefix' => 'inbox', 'middleware' => ['auth:admin', 'module:crm_section,update'], 'as' => 'inbox.'], function () {
            Route::post('{id}/activity', [InboxMessageController::class, 'storeActivity'])->name('activity.store');
            Route::post('{id}/note', [InboxMessageController::class, 'storeNote'])->name('note.store');
            Route::post('{id}/task', [InboxMessageController::class, 'storeTask'])->name('task.store');
            Route::post('{id}/call', [InboxMessageController::class, 'storeCall'])->name('call.store');
            Route::post('{id}/file', [InboxMessageController::class, 'storeFile'])->name('file.store');
            Route::put('{id}/task/{task_id}', [InboxMessageController::class, 'updateTask'])->name('task.update');
            Route::post('{id}/task/{task_id}/complete', [InboxMessageController::class, 'completeTask'])->name('task.complete');
        });
        Route::group(['prefix' => 'deal', 'middleware' => ['auth:admin', 'module:crm_section,update'], 'as' => 'deal.'], function () {
            Route::post('{id}/activity', [DealController::class, 'storeActivity'])->name('activity.store');
            Route::post('{id}/note', [DealController::class, 'storeNote'])->name('note.store');
            Route::post('{id}/task', [DealController::class, 'storeTask'])->name('task.store');
            Route::post('{id}/call', [DealController::class, 'storeCall'])->name('call.store');
            Route::post('{id}/file', [DealController::class, 'storeFile'])->name('file.store');
            Route::put('{id}/task/{task_id}', [DealController::class, 'updateTask'])->name('task.update');
            Route::post('{id}/task/{task_id}/complete', [DealController::class, 'completeTask'])->name('task.complete');
        });
        Route::group(['prefix' => 'deals', 'as' => 'deals.'], function () {

            /*
    |--------------------------------------------------------------------------
    | 🟦 WHOLESALE DEALS ROUTES
    |--------------------------------------------------------------------------
    */
            Route::group(['prefix' => 'wholesale', 'as' => 'wholesale.'], function () {
                Route::middleware('module:crm_section')->group(function () {
                    Route::controller(DealController::class)->group(function () {

                        Route::get(Deals::INDEX[URI], 'index')
                            ->name('index')
                            ->middleware('module:crm_section,deal_wholesale_list');

                        Route::get(Deals::VIEW[URI] . '/{id}', 'view')
                            ->name('view')
                            ->middleware('module:crm_section,deal_wholesale_view');

                        Route::post(Deals::REQUEST_QUOTATION[URI] . '/{id}', 'requestQuotation')
                            ->name('request-quotation')
                            ->middleware('module:crm_section,deal_wholesale_request_quotation');

                        Route::post(Deals::DESQUALIFY[URI], 'disqualify')
                            ->name('disqualify')
                            ->middleware('module:crm_section,deal_wholesale_disqualify');

                        Route::get(Deals::DEPARTMENT_EMPLOYEE[URI], 'getEmployeesByDepartment')
                            ->name('getemployee');

                        Route::post(Deals::ASSIGN_EMPLOYEE[URI], 'assignEmployee')
                            ->name('employee-assign')
                            ->middleware('module:crm_section,deal_wholesale_assign_employee');

                        Route::post(Deals::ASSIGN_OWNER[URI], 'assignOwner')
                            ->name('owner-assign')
                            ->middleware('module:crm_section,deal_wholesale_assign_owner');

                        Route::post(Deals::ASSIGN_DEPARTMENT[URI], 'updateTicketDepartment')
                            ->name('update-ticket-department')
                            ->middleware('module:crm_section,deal_wholesale_assign_department');

                        Route::get(Deals::EXPORT[URI], 'exportList')
                            ->name('export')
                            ->middleware('module:crm_section,deal_wholesale_export');
                    });
                });
            });

            Route::group(['prefix' => 'retail', 'as' => 'retail.'], function () {
                Route::middleware('module:crm_section')->group(function () {
                    Route::controller(DealController::class)->group(function () {

                        Route::get(Deals::RETAILER[URI], 'getRetailView')
                            ->name('list')
                            ->middleware('module:crm_section,deal_retail_list');

                        Route::get(Deals::VIEW[URI] . '/{id}', 'retailView')
                            ->name('view')
                            ->middleware('module:crm_section,deal_retail_view');

                        Route::post(Deals::REQUEST_QUOTATION[URI] . '/{id}', 'requestQuotation')
                            ->name('request-quotation')
                            ->middleware('module:crm_section,deal_retail_request_quotation');

                        Route::post(Deals::DESQUALIFY[URI], 'disqualify')
                            ->name('disqualify')
                            ->middleware('module:crm_section,deal_retail_disqualify');

                        Route::get(Deals::DEPARTMENT_EMPLOYEE[URI], 'getEmployeesByDepartment')
                            ->name('getemployee');

                        Route::post(Deals::ASSIGN_EMPLOYEE[URI], 'assignEmployee')
                            ->name('employee-assign')
                            ->middleware('module:crm_section,deal_retail_assign_employee');

                        Route::post(Deals::ASSIGN_OWNER[URI], 'assignOwner')
                            ->name('owner-assign')
                            ->middleware('module:crm_section,deal_retail_assign_owner');

                        Route::post(Deals::ASSIGN_DEPARTMENT[URI], 'updateTicketDepartment')
                            ->name('update-ticket-department')
                            ->middleware('module:crm_section,deal_retail_assign_department');

                        Route::get(Deals::EXPORT[URI], 'exportList')
                            ->name('export')
                            ->middleware('module:crm_section,deal_retail_export');

                        Route::post(Deals::LINK_ORDER[URI], 'linkOrder')
                            ->name('link-order')
                            ->middleware('module:crm_section,deal_retail_link_order');

                        Route::get(Deals::GET_USER_DATA[URI], 'getUserOrders')
                            ->name('get-user-orders')
                            ->middleware('module:crm_section,deal_retail_get_user_data');
                    });
                });
            });
        });
        Route::group(['prefix' => 'deals', 'as' => 'deals.'], function () {
            Route::middleware('module:crm_section,update')->group(function () {
                Route::post('/retail/escalate', [DealController::class, 'escalateRetail'])->name('retail.escalate');
                Route::post('/wholesale/escalate', [DealController::class, 'escalateWholesale'])->name('wholesale.escalate');
            });
        });
    });

    Route::group(['prefix' => 'wholesale', 'as' => 'wholesale.'], function () {
        Route::group(['prefix' => 'product', 'as' => 'product.', 'middleware' => ['module:wholesaler_section']], function () {

            // Read permissions
            Route::controller(WholeSaleProductController::class)->group(function () {
                Route::get(WholeSalesProducts::LIST[URI], 'index')->middleware('module:wholesaler_section,product_list')->name('list');
                Route::get(WholeSalesProducts::PRODUCT_VIEW[URI] . '/{product_id}', 'getProductView')->middleware('module:wholesaler_section,product_view')->name('view');
                Route::get(WholeSalesProducts::EXPORT_EXCEL[URI], 'exportProductWithPrices')->middleware('module:wholesaler_section,product_list')->name('export-excel');
                Route::get(WholeSalesProducts::BUSINESS_REQUEST[URI], 'getWholesalerBusinessRequests')->middleware('module:wholesaler_section,product_list')->name('business-request');
            });

            // Create permissions
            Route::controller(WholeSaleProductController::class)->group(function () {
                Route::get(WholeSalesProducts::ADD[URI], 'getAddView')->middleware('module:wholesaler_section,product_add')->name('add');
                Route::get(WholeSalesProducts::GET_VARIATION_PRICE[URI] . '/{id}', 'getVariationsWithPrice')->middleware('module:wholesaler_section,product_add')->name('get-variations');
                Route::post(WholeSalesProducts::ADD[URI], 'add')->middleware('module:wholesaler_section,product_add')->name('add');
            });

            // Update permissions
            Route::controller(WholeSaleProductController::class)->group(function () {
                Route::get(WholeSalesProducts::UPDATE_VIEW[URI] . '/{product_id}', 'getUpdateView')->middleware('module:wholesaler_section,product_edit')->name('edit');
                Route::post(WholeSalesProducts::UPDATE[URI] . '/{id}', 'update')->middleware('module:wholesaler_section,product_edit')->name('update');
                Route::get(WholeSalesProducts::PRODUCT_TOGGLE[URI] . '/{id}', 'toggleStatusProduct')->middleware('module:wholesaler_section,product_status')->name('toggle-status');
            });

            // Delete permissions
            Route::controller(WholeSaleProductController::class)->group(function () {
                Route::delete(WholeSalesProducts::PRODUCT_DELETE[URI] . '/{id}', 'destroy')->middleware('module:wholesaler_section,product_delete')->name('delete');
            });
        });

        Route::group(['prefix' => 'business', 'as' => 'business.', 'middleware' => ['module:wholesaler_section']], function () {
            Route::controller(WholeSalerController::class)->group(function () {
                Route::get(WholeSaler::LIST[URI], 'index')->middleware('module:wholesaler_section,wholesaler_view')->name('list');
                Route::get(WholeSaler::WHOLESALE_QUOTATIONS[URI], 'wholesaleQuotation')->middleware('module:wholesaler_section,quotation_view')->name('wholesale.order');
                Route::get(WholeSaler::CONFIRMED_ORDERS[URI], 'confirmedOrders')->middleware('module:wholesaler_section,confirme_order_view')->name('wholesale.confirmedorder');
                Route::get(WholeSaler::LIST_REQUEST[URI], 'wholesalerRequest')->middleware('module:wholesaler_section,wholesaler_join_request')->name('request');
                Route::get(WholeSaler::WHOLESALER_VIEW[URI] . '/{id}', 'viewWholesalerDetails')->middleware('module:wholesaler_section,wholesaler_view')->name('wholesaler.profile');
                Route::get(WholeSaler::WHOLESALER_EDIT[URI] . '/{id}', 'viewWholesalerEdit')->middleware('module:wholesaler_section,wholesaler_update')->name('wholesaler.profile.edit');
                Route::get(WholeSaler::REQUEST_VIEW[URI], 'orderRequest')->middleware('module:wholesaler_section,purchase_request_view')->name('order.request');
                Route::get(WholeSaler::ORDER_REQUEST[URI] . '/{id}', 'viewOrder')->middleware('module:wholesaler_section,purchase_request_view')->name('order.view');
                Route::get(WholeSaler::PURCHASE_ORDER_VIEW[URI] . '/{id}', 'viewPurchaseOrder')->middleware('module:wholesaler_section,purchase_request_view')->name('purchase.order.view');
                Route::get(WholeSaler::INVOICE_VIEW[URI] . '/{id}', 'invoiceView')->middleware('module:wholesaler_section,quotation_view')->name('orders.invoice');
                Route::get(WholeSaler::INVOICE_EDIT[URI] . '/{id}', 'invoiceEdit')->middleware('module:wholesaler_section,quotation_update')->name('orders.invoice.edit');
                Route::get(WholeSaler::TIER_VIEW[URI], 'tierIndex')->middleware('module:wholesaler_section,tier_view')->name('wholesaler.tier.view');
                Route::get(WholeSaler::CHECK_ORDER_NO[URI], 'checkOrderNo')->name('check-order-no');
                Route::get(WholeSaler::PAYMENT_VIEW[URI] . '/{id}', 'showPaymentPage')->middleware('module:wholesaler_section,confirem_order_payment')->name('orders.payment');
                Route::get(WholeSaler::DELIVERY_VIEW[URI] . '/{id}', 'showDeliveryPage')->middleware('module:wholesaler_section,confirem_order_delivery')->name('orders.delivery');
                Route::get(WholeSaler::DOWNLOAD_CSV[URI] . '/{id}', 'downloadCsv')->middleware('module:wholesaler_section,confirem_order_delivery')->name('delivery.download-csv');
                Route::get(WholeSaler::ORDER_CHECK[URI], 'checkNumber')->name('order.check-number');
                Route::get(WholeSaler::BRANCH_LIST[URI], 'branchList')->name('branch-list');
                Route::get(WholeSaler::BRANCH_PRODUCT_STORE[URI], 'branchProductStock')->name('branch-product-store');
                Route::get(WholeSaler::CONFIRM_TRACKING_PAGE[URI] . '/{id}', 'showTrackingPage')->middleware('module:wholesaler_section,confirem_order_delivery')->name('confirm-order.tracking-page');
                Route::get(WholeSaler::CONFIREM_ORDER_INVOICE[URI] . '/{id}', 'showCompleteInvoice')->middleware('module:wholesaler_section,confirem_order_invoice')->name('confirm-order.complete.invoice');
                Route::get(WholeSaler::EXPORT_WHOLESALER_REQ[URI], 'exportReqList')->middleware('module:wholesaler_section,wholesaler_join_request')->name('wholesale-req.export');
                Route::get(WholeSaler::EXPORT_WHOLESALER[URI], 'exportWholesalerList')->middleware('module:wholesaler_section,wholesaler_view')->name('wholesaler.export');
                Route::get(WholeSaler::EXPORT_WHOLESALER_CONFIRM[URI], 'exporConfirmList')->middleware('module:wholesaler_section,confirme_order_view')->name('wholesale-confirm.export');
                Route::get(WholeSaler::EXPORT_WHOLESALER_PURCHASE[URI], 'exporPurchaseList')->middleware('module:wholesaler_section,purchase_request_view')->name('wholesale-purchase.export');
                Route::get(WholeSaler::EXPORT_WHOLESALER_QUOT[URI], 'exportQuotationList')->middleware('module:wholesaler_section,quotation_view')->name('wholesale-quotation.export');
                Route::get(WholeSaler::ORDER_BY_TYPE[URI], 'getByType')->middleware('module:wholesaler_section,wholesaler_view')->name('orders.by-type');
                Route::get(WholeSaler::ORDER_HISTORY[URI] . '/{order}', 'getOrderStatusHistory')->name('ajax-activity-history');
            });

            Route::controller(WholeSalerController::class)->group(function () {
                Route::get(WholeSaler::CREATE_QUOTATION[URI], 'createQuotation')->middleware('module:wholesaler_section,create_quotation')->name('create-quotation');
                Route::post(WholeSaler::STORE_QUOTATION[URI], 'storeQuotation')->middleware('module:wholesaler_section,create_quotation')->name('store-quotation');
                Route::post(WholeSaler::ORDER_APPROVE[URI] . '/{id}', 'quotationCreate')->middleware('module:wholesaler_section,create_quotation')->name('orders.approve');
                Route::post(WholeSaler::PAYMENT_STORE[URI], 'paymentStore')->middleware('module:wholesaler_section,confirem_order_payment')->name('orders.payment.add');
                Route::post(WholeSaler::ORDER_ASSIGN[URI], 'assignNumber')->middleware('module:wholesaler_section,assign_purchase_order_no')->name('order.assign-number');
                Route::post(WholeSaler::CONFIRMED_ORDER_NO[URI], 'assignConfirmNumber')->middleware('module:wholesaler_section,assign_confirm_order_no')->name('order.assign-confirm-no');
                Route::post(WholeSaler::CONFIRMED_INVOICE_NO[URI], 'assignInvoiceNumber')->middleware('module:wholesaler_section,assign_invoice_no')->name('order.assign-invoice-no');
                Route::post(WholeSaler::CONFIRMED_ORDER_DELIVERY_STORE[URI], 'deliveryStore')->middleware('module:wholesaler_section,confirem_order_delivery')->name('order.delivery.store');
                Route::post(WholeSaler::MOQ_TOGGLE_OWERRIDE[URI], 'toggleMOQOverride')->middleware('module:wholesaler_section,wholesaler_moq_owerride')->name('toggle.moq');
                Route::post(WholeSaler::TIER_ADD[URI], 'tierStore')->middleware('module:wholesaler_section,tier_add')->name('wholesaler.tier.add');
                Route::get(WholeSaler::CHECK_CONFIRMED_ORDER_NO[URI], 'checkConfirmNumber')->name('order.check-confirm-invoice-no');
            });

            // Update permissions
            Route::controller(WholeSalerController::class)->group(function () {
                Route::post(WholeSaler::WHOLESALER_STATUS[URI], 'approveRejectWholesaleBusiness')->middleware('module:wholesaler_section,wholesaler_join_request')->name('approve-reject');
                Route::post(WholeSaler::WHOLESALER_CONTACT[URI], 'wholesalerContact')->middleware('module:wholesaler_section,wholesaler_contact')->name('wholsaler-contect');
                Route::put(WholeSaler::WHOLESALER_CONTACT_UPDATE[URI] . '/{id}', 'wholesalerContactUpdate')->middleware('module:wholesaler_section,wholesaler_contact')->name('wholsaler-contect.update');
                Route::post(WholeSaler::INVOICE_UPDATE[URI] . '/{id}', 'quotationUpdate')->middleware('module:wholesaler_section,quotation_update')->name('invoice.update');
                Route::post(WholeSaler::WHOLESALER_UPDATE[URI] . '/{id}', 'wholesalerUpdate')->middleware('module:wholesaler_section,wholesaler_update')->name('wholesaler.update');
                Route::put(WholeSaler::TIER_UPDATE[URI] . '/{id}', 'tierUpdate')->middleware('module:wholesaler_section,tier_edit')->name('wholesaler.tier.update');
                Route::post(WholeSaler::TIER_STATUS_UPDATE[URI], 'tierUpdateStatus')->middleware('module:wholesaler_section,tier_edit')->name('wholesaler.tier.status');
            });

            Route::controller(WholeSalerController::class)->group(function () {
                Route::get(WholeSaler::REQUEST_DELETE[URI] . '/{id}', 'orderDestroy')->middleware('module:wholesaler_section,purchase_request_delete')->name('order.delete');
                Route::get(WholeSaler::QUOTATION_DELETE[URI] . '/{id}', 'quotationDestroy')->middleware('module:wholesaler_section,quotation_delete')->name('quotation.delete');
                Route::get(WholeSaler::CONFIREM_ORDER_DELETE[URI] . '/{id}', 'confiremOrderDestroy')->middleware('module:wholesaler_section,confirem_order_delete')->name('confirem.order.delete');
                Route::delete(WholeSaler::TIER_DELETE[URI] . '/{id}', 'tierDestroy')->middleware('module:wholesaler_section,tier_delete')->name('wholesaler.tier.delete');
                Route::patch(WholeSaler::WHOLESALER_CONTACT_DELETE[URI] . '/{id}', 'wholesalerContactDelete')->middleware('module:wholesaler_section,wholesaler_contact')->name('wholsaler-contect.softDelete');
            });
        });

        Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.', 'middleware' => ['module:wholesaler_section']], function () {
            Route::middleware('module:wholesaler_section,wholesaler_dashboard')->group(function () {
                Route::controller(WholesaleDashboardController::class)->group(function () {
                    Route::get(WholeSaler::DASHBOARD[URI], 'index')->middleware('module:wholesaler_section,wholesaler_dashboard')->name('index');
                    Route::post(WholeSaler::ORDER_STATUS[URI], 'getOrderStatus')->middleware('module:wholesaler_section,wholesaler_dashboard')->name('order-status');
                    Route::get(WholeSaler::EARNING_STATISTICS[URI], 'getEarningStatistics')->middleware('module:wholesaler_section,wholesaler_dashboard')->name('earning-statistics');
                    Route::get(WholeSaler::ORDER_STATISTICS[URI], 'getOrderStatistics')->middleware('module:wholesaler_section,wholesaler_dashboard')->name('order-statistics');
                    Route::get(WholeSaler::REAL_TIME_ACTIVITIES[URI], 'getRealTimeActivities')->middleware('module:wholesaler_section,wholesaler_dashboard')->name('real-time-activities');
                });
            });
        });
    });


    Route::group(['prefix' => 'messages', 'as' => 'messages.'], function () {
        Route::controller(ChattingController::class)->group(function () {
            Route::get(Chatting::INDEX[URI] . '/{type}', 'index')->name('index')->middleware('module:crm_section,chat_box_view');
            Route::get(Chatting::MESSAGE[URI], 'getMessageByUser')->name('message');
            Route::post(Chatting::MESSAGE[URI], 'addAdminMessage');
            Route::get(Chatting::NEW_NOTIFICATION[URI], 'getNewNotification')->name('new-notification');
        });
    });

    Route::group(['prefix' => 'contact', 'as' => 'contact.', 'middleware' => ['module:crm_section']], function () {

        // Read permissions
        Route::middleware('module:crm_section,read')->group(function () {
            Route::controller(ContactController::class)->group(function () {
                Route::get(Contact::LIST[URI], 'index')->name('list');
                Route::get(Contact::VIEW[URI] . '/{id}', 'getView')->name('view');
                Route::post(Contact::FILTER[URI], 'getListByFilter')->name('filter');
            });
        });

        // Create permissions
        Route::middleware('module:crm_section,create')->group(function () {
            Route::controller(ContactController::class)->group(function () {
                Route::post(Contact::ADD[URI], 'add')->name('store');
            });
        });

        // Update permissions
        Route::middleware('module:crm_section,update')->group(function () {
            Route::controller(ContactController::class)->group(function () {
                Route::post(Contact::UPDATE[URI] . '/{id}', 'update')->name('update');
                Route::post(Contact::SEND_MAIL[URI] . '/{id}', 'sendMail')->name('send-mail');
            });
        });

        // Delete permissions
        Route::middleware('module:crm_section,delete')->group(function () {
            Route::controller(ContactController::class)->group(function () {
                Route::post(Contact::DELETE[URI], 'delete')->name('delete');
            });
        });
    });

    Route::group(['prefix' => 'delivery-man', 'as' => 'delivery-man.', 'middleware' => ['module:user_section']], function () {

        // Read permissions
        Route::middleware('module:user_section,read')->group(function () {
            Route::controller(DeliveryManController::class)->group(function () {
                Route::get(DeliveryMan::LIST[URI], 'index')->name('list');
                Route::get(DeliveryMan::ADD[URI], 'getAddView')->name('add');  // view add form
                Route::get(DeliveryMan::EXPORT[URI], 'exportList')->name('export');
                Route::get(DeliveryMan::UPDATE[URI] . '/{id}', 'getUpdateView')->name('edit'); // view edit form
                Route::get(DeliveryMan::EARNING_STATEMENT_OVERVIEW[URI] . '/{id}', 'getEarningOverview')->name('earning-statement-overview');
                Route::get(DeliveryMan::EARNING_OVERVIEW[URI] . '/{id}', 'getOrderWiseEarningView')->name('order-wise-earning');
                Route::post(DeliveryMan::ORDER_WISE_EARNING_LIST_BY_FILTER[URI] . '/{id}', 'getOrderWiseEarningListByFilter')->name('order-wise-earning-list-by-filter');
                Route::get(DeliveryMan::ORDER_HISTORY_LOG[URI] . '/{id}', 'getOrderHistoryList')->name('order-history-log');
                Route::get(DeliveryMan::ORDER_HISTORY_LOG_EXPORT[URI] . '/{id}', 'getOrderHistoryListExport')->name('order-history-log-export');
                Route::get(DeliveryMan::RATING[URI] . '/{id}', 'getRatingView')->name('rating');
                Route::get(DeliveryMan::ORDER_HISTORY[URI] . '/{order}', 'getOrderStatusHistory')->name('ajax-order-status-history');
            });

            Route::controller(DeliveryManCashCollectController::class)->group(function () {
                Route::get(DeliveryManCash::LIST[URI] . '/{id}', 'index')->name('collect-cash');
            });

            Route::controller(DeliverymanWithdrawController::class)->group(function () {
                Route::get(DeliverymanWithdraw::LIST[URI], 'index')->name('withdraw-list');
                Route::get(DeliverymanWithdraw::EXPORT_LIST[URI], 'exportList')->name('withdraw-list-export');
                Route::get(DeliverymanWithdraw::VIEW[URI] . '/{withdraw_id}', 'getView')->name('withdraw-view');
            });

            Route::group(['prefix' => 'emergency-contact', 'as' => 'emergency-contact.'], function () {
                Route::controller(EmergencyContactController::class)->group(function () {
                    Route::get(EmergencyContact::LIST[URI], 'index')->name('index');
                    Route::get(EmergencyContact::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                });
            });
        });

        // Create permissions
        Route::middleware('module:user_section,create')->group(function () {
            Route::controller(DeliveryManController::class)->group(function () {
                Route::post(DeliveryMan::ADD[URI], 'add');
            });

            Route::controller(DeliveryManCashCollectController::class)->group(function () {
                Route::post(DeliveryManCash::ADD[URI] . '/{id}', 'getCashReceive')->name('cash-receive');
            });

            Route::group(['prefix' => 'emergency-contact', 'as' => 'emergency-contact.'], function () {
                Route::controller(EmergencyContactController::class)->group(function () {
                    Route::post(EmergencyContact::ADD[URI], 'add')->name('add');
                });
            });
        });

        // Update permissions
        Route::middleware('module:user_section,update')->group(function () {
            Route::controller(DeliveryManController::class)->group(function () {
                Route::post(DeliveryMan::UPDATE[URI] . '/{id}', 'update')->name('update');
                Route::post(DeliveryMan::STATUS[URI], 'updateStatus')->name('status-update');
            });

            Route::controller(DeliverymanWithdrawController::class)->group(function () {
                Route::post(DeliverymanWithdraw::UPDATE[URI] . '/{id}', 'updateStatus')->name('withdraw-update-status');
                Route::post(DeliverymanWithdraw::LIST[URI], 'getFiltered');
            });

            Route::group(['prefix' => 'emergency-contact', 'as' => 'emergency-contact.'], function () {
                Route::controller(EmergencyContactController::class)->group(function () {
                    Route::post(EmergencyContact::UPDATE[URI] . '/{id}', 'update');
                    Route::post(EmergencyContact::STATUS[URI], 'updateStatus')->name('ajax-status-change');
                });
            });
        });

        // Delete permissions
        Route::middleware('module:user_section,delete')->group(function () {
            Route::controller(DeliveryManController::class)->group(function () {
                Route::delete(DeliveryMan::DELETE[URI] . '/{id}', 'delete')->name('delete');
            });

            Route::group(['prefix' => 'emergency-contact', 'as' => 'emergency-contact.'], function () {
                Route::controller(EmergencyContactController::class)->group(function () {
                    Route::delete(EmergencyContact::DELETE[URI], 'delete')->name('destroy');
                });
            });
        });
    });


    Route::group(['prefix' => 'most-demanded', 'as' => 'most-demanded.', 'middleware' => ['module:promotion_management']], function () {

        // Read permissions
        Route::middleware('module:promotion_management,read')->group(function () {
            Route::controller(MostDemandedController::class)->group(function () {
                Route::get(MostDemanded::LIST[URI], 'index')->name('index');
                Route::get(MostDemanded::UPDATE[URI] . '/{id}', 'getUpdateView')->name('edit');
            });
        });

        // Create permissions
        Route::middleware('module:promotion_management,create')->group(function () {
            Route::controller(MostDemandedController::class)->group(function () {
                Route::post(MostDemanded::ADD[URI], 'add')->name('store');
            });
        });

        // Update permissions
        Route::middleware('module:promotion_management,update')->group(function () {
            Route::controller(MostDemandedController::class)->group(function () {
                Route::post(MostDemanded::UPDATE[URI] . '/{id}', 'update')->name('update');
                Route::post(MostDemanded::STATUS[URI], 'updateStatus')->name('status-update');
            });
        });

        // Delete permissions
        Route::middleware('module:promotion_management,delete')->group(function () {
            Route::controller(MostDemandedController::class)->group(function () {
                Route::post(MostDemanded::DELETE[URI], 'delete')->name('delete');
            });
        });
    });

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.', 'middleware' => ['module:cms_section']], function () {

        // Read permissions
        Route::middleware('module:cms_section,read')->group(function () {
            Route::controller(AllPagesBannerController::class)->group(function () {
                Route::get(AllPagesBanner::LIST[URI], 'index')->name('all-pages-banner');
                Route::get(AllPagesBanner::UPDATE[URI] . '/{id}', 'getUpdateView')->name('all-pages-banner-edit');
            });
        });

        // Create permissions
        Route::middleware('module:cms_section,create')->group(function () {
            Route::controller(AllPagesBannerController::class)->group(function () {
                Route::post(AllPagesBanner::ADD[URI], 'add')->name('all-pages-banner-store');
            });
        });

        // Update permissions
        Route::middleware('module:cms_section,update')->group(function () {
            Route::controller(AllPagesBannerController::class)->group(function () {
                Route::post(AllPagesBanner::UPDATE[URI], 'update')->name('all-pages-banner-update');
                Route::post(AllPagesBanner::STATUS[URI], 'updateStatus')->name('all-pages-banner-status');
            });
        });

        // Delete permissions
        Route::middleware('module:cms_section,delete')->group(function () {
            Route::controller(AllPagesBannerController::class)->group(function () {
                Route::post(AllPagesBanner::DELETE[URI], 'delete')->name('all-pages-banner-delete');
            });
        });
    });


    Route::group(['prefix' => 'system-setup', 'as' => 'system-setup.'], function () {
        Route::group(['prefix' => 'login-settings', 'as' => 'login-settings.'], function () {

            // Read permissions
            Route::middleware('module:system_settings,read')->group(function () {
                Route::controller(SystemLoginSetupController::class)->group(function () {
                    Route::get(SystemSetup::CUSTOMER_LOGIN_SETUP[URI], 'getCustomerLoginSetupView')->name('customer-login-setup');
                    Route::get(SystemSetup::OTP_SETUP[URI], 'getOtpSetupView')->name('otp-setup');
                    Route::get(SystemSetup::LOGIN_URL_SETUP[URI], 'getLoginSetupView')->name('login-url-setup');
                });
            });

            // Update permissions
            Route::middleware('module:system_settings,update')->group(function () {
                Route::controller(SystemLoginSetupController::class)->group(function () {
                    Route::post(SystemSetup::CUSTOMER_LOGIN_SETUP[URI], 'updateCustomerLoginSetup')->name('customer-login-setup-update');
                    Route::post(SystemSetup::CUSTOMER_CONFIG_VALIDATION[URI], 'getConfigValidation')->name('config-status-validation');
                    Route::post(SystemSetup::OTP_SETUP[URI], 'updateOtpSetup')->name('otp-setup-update');
                    Route::post(SystemSetup::LOGIN_URL_SETUP[URI], 'updateLoginSetupView')->name('login-url-setup-update');
                });
            });
        });
    });


    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
        Route::group(['middleware' => ['module:system_settings']], function () {
            Route::middleware('module:system_settings,read')->controller(PagesController::class)->group(function () {
                Route::get(Pages::TERMS_CONDITION[URI], 'index')->name('terms-condition');
                Route::get(Pages::SERVICE_POLICY[URI], 'getServicePolicyView')->name('service-policy');
                Route::get(Pages::WARRANTY_POLICY[URI], 'getWarrantyPolicyView')->name('warranty-policy');
                Route::get(Pages::PRIVACY_POLICY[URI], 'getPrivacyPolicyView')->name('privacy-policy');
                Route::get(Pages::ABOUT_US[URI], 'getAboutUsView')->name('about-us');
                Route::get(Pages::VIEW[URI] . '/{page}', 'getPageView')->name('page');
                Route::get(Pages::COOKIE_SETTINGS[URI], 'getCookieSettingsView')->name('cookie-settings');
            });

            // Update
            Route::middleware('module:system_settings,update')->controller(PagesController::class)->group(function () {
                Route::post(Pages::TERMS_CONDITION[URI], 'updateTermsCondition')->name('update-terms');
                Route::post(Pages::SERVICE_POLICY[URI], 'updateServicePolicy')->name('update-service');
                Route::post(Pages::WARRANTY_POLICY[URI], 'updateWarrantyPolicy')->name('update-warranty');
                Route::post(Pages::PRIVACY_POLICY[URI], 'updatePrivacyPolicy')->name('privacy-policy-update');
                Route::post(Pages::ABOUT_US[URI], 'updateAboutUs')->name('about-update');
                Route::post(Pages::VIEW[URI] . '/{page}', 'updatePage')->name('page-update');
                Route::post(Pages::COOKIE_SETTINGS[URI], 'updateCookieSetting')->name('cookie-settings-update');
            });

            Route::middleware('module:system_settings,read')->controller(SocialMediaSettingsController::class)->group(function () {
                Route::get(SocialMedia::VIEW[URI], 'index')->name('social-media');
                Route::get(SocialMedia::LIST[URI], 'getList')->name('fetch');
            });
            Route::middleware('module:system_settings,read')->controller(StateCityController::class)->group(function () {
                Route::get('state-city',  'index')->name('state-city.index');
                Route::post('state-city/state',  'storeState')->name('state-city.state.store');
                Route::delete('state-city/state/{id}',  'deleteState')->name('state-city.state.delete');
                Route::post('state-city/city',  'storeCity')->name('state-city.city.store');
                Route::post('state-city/area',  'storeArea')->name('state-city.area.store');
                Route::delete('state-city/city/{id}',  'deleteCity')->name('state-city.city.delete');
                Route::delete('state-city/area/{id}',  'deleteArea')->name('state-city.area.delete');
            });

            // Create
            Route::middleware('module:system_settings,create')->controller(SocialMediaSettingsController::class)->group(function () {
                Route::post(SocialMedia::ADD[URI], 'add')->name('social-media-store');
            });

            // Update
            Route::middleware('module:system_settings,update')->controller(SocialMediaSettingsController::class)->group(function () {
                Route::post(SocialMedia::GET_UPDATE[URI], 'getUpdate')->name('social-media-edit');
                Route::post(SocialMedia::UPDATE[URI], 'update')->name('social-media-update');
                Route::post(SocialMedia::STATUS[URI], 'updateStatus')->name('social-media-status-update');
            });

            // Delete
            Route::middleware('module:system_settings,delete')->controller(SocialMediaSettingsController::class)->group(function () {
                Route::post(SocialMedia::DELETE[URI], 'delete')->name('social-media-delete');
            });

            // -------------------- BusinessSettingsController --------------------

            // Read
            Route::middleware('module:system_settings,read')->controller(BusinessSettingsController::class)->group(function () {
                Route::get(BusinessSettings::ANALYTICS_INDEX[URI], 'getAnalyticsView')->name('analytics-index');
            });

            // Update
            Route::middleware('module:system_settings,update')->controller(BusinessSettingsController::class)->group(function () {
                Route::post(BusinessSettings::MAINTENANCE_MODE[URI], 'updateSystemMode')->name('maintenance-mode');
                Route::post(BusinessSettings::ANALYTICS_UPDATE[URI], 'updateAnalytics')->name('analytics-update');
            });

            // -------------------- RecaptchaController --------------------

            // Read
            Route::middleware('module:system_settings,read')->controller(RecaptchaController::class)->group(function () {
                Route::get(Recaptcha::VIEW[URI], 'index')->name('captcha');
            });

            // Update
            Route::middleware('module:system_settings,update')->controller(RecaptchaController::class)->group(function () {
                Route::post(Recaptcha::VIEW[URI], 'update')->name('captcha-update');
            });

            // -------------------- GoogleMapAPIController --------------------

            // Read
            Route::middleware('module:system_settings,read')->controller(GoogleMapAPIController::class)->group(function () {
                Route::get(GoogleMapAPI::VIEW[URI], 'index')->name('map-api');
            });

            // Update
            Route::middleware('module:system_settings,update')->controller(GoogleMapAPIController::class)->group(function () {
                Route::post(GoogleMapAPI::VIEW[URI], 'update')->name('map-api-update');
            });

            // -------------------- FeaturesSectionController --------------------

            // Read
            Route::middleware('module:system_settings,read')->controller(FeaturesSectionController::class)->group(function () {
                Route::get(FeaturesSection::VIEW[URI], 'index')->name('features-section');
                Route::get(FeaturesSection::COMPANY_RELIABILITY[URI], 'getCompanyReliabilityView')->name('company-reliability');
            });

            // Update
            Route::middleware('module:system_settings,update')->controller(FeaturesSectionController::class)->group(function () {
                Route::post(FeaturesSection::UPDATE[URI], 'update')->name('features-section.submit');
                Route::post(FeaturesSection::COMPANY_RELIABILITY[URI], 'updateCompanyReliability')->name('company-reliability-update');
            });
        });

        Route::group(['prefix' => 'language', 'as' => 'language.'], function () {

            // ----------- Read Permissions -----------
            Route::middleware('module:system_settings,read')->controller(LanguageController::class)->group(function () {
                Route::get(Language::LIST[URI], 'index')->name('index');
                Route::get(Language::TRANSLATE_VIEW[URI] . '/{lang}', 'getTranslateView')->name('translate');
                Route::get(Language::TRANSLATE_LIST[URI] . '/{lang}', 'getTranslateList')->name('translate.list');
            });

            // ----------- Create Permissions -----------
            Route::middleware('module:system_settings,create')->controller(LanguageController::class)->group(function () {
                Route::post(Language::ADD[URI], 'add')->name('add-new');
            });

            // ----------- Update Permissions -----------
            Route::middleware('module:system_settings,update')->controller(LanguageController::class)->group(function () {
                Route::post(Language::STATUS[URI], 'updateStatus')->name('update-status');
                Route::get(Language::DEFAULT_STATUS[URI], 'updateDefaultStatus')->name('update-default-status');
                Route::post(Language::UPDATE[URI], 'update')->name('update');
                Route::post(Language::TRANSLATE_ADD[URI] . '/{lang}', 'updateTranslate')->name('translate-submit');
                Route::any(Language::TRANSLATE_AUTO[URI] . '/{lang}', 'getAutoTranslate')->name('auto-translate');
                Route::any(Language::TRANSLATE_AUTO_ALL[URI] . '/{lang}', 'getAutoTranslateAllMessages')->name('auto-translate-all');
            });

            // ----------- Delete Permissions -----------
            Route::middleware('module:system_settings,delete')->controller(LanguageController::class)->group(function () {
                Route::get(Language::DELETE[URI] . '/{lang}', 'delete')->name('delete');
                Route::post(Language::TRANSLATE_REMOVE[URI] . '/{lang}', 'deleteTranslateKey')->name('remove-key');
            });
        });


        Route::group(['prefix' => 'invoice-settings', 'as' => 'invoice-settings.'], function () {

            // ----------- Read Permissions -----------
            Route::middleware('module:system_settings,read')->controller(InvoiceSettingsController::class)->group(function () {
                Route::get(InvoiceSettings::VIEW[URI], 'index')->name('index');
            });

            // ----------- Update Permissions -----------
            Route::middleware('module:system_settings,update')->controller(InvoiceSettingsController::class)->group(function () {
                Route::post(InvoiceSettings::VIEW[URI], 'update')->name('update');
            });
        });



        Route::group(['prefix' => 'quotation-settings', 'as' => 'quotation-settings.'], function () {
            Route::middleware('module:system_settings,read')->controller(QuotationSettingsController::class)->group(function () {
                Route::get(QuotationSettings::VIEW[URI], 'index')->name('index');
            });
            Route::middleware('module:system_settings,update')->controller(QuotationSettingsController::class)->group(function () {
                Route::post(QuotationSettings::VIEW[URI], 'update')->name('update');
            });
        });



        Route::group(['prefix' => 'web-config', 'as' => 'web-config.'], function () {

            // ----------- Read Permissions -----------
            Route::middleware('module:system_settings,read')->group(function () {
                Route::controller(BusinessSettingsController::class)->group(function () {
                    Route::get(BusinessSettings::INDEX[URI], 'index')->name('index');
                    Route::get(BusinessSettings::APP_SETTINGS[URI], 'getAppSettingsView')->name('app-settings');
                    Route::get(BusinessSettings::CACHE[URI], 'getCacheSettingsView')->name('cache-settings');
                });

                Route::controller(EnvironmentSettingsController::class)->group(function () {
                    Route::get(EnvironmentSettings::VIEW[URI], 'index')->name('environment-setup');
                });

                Route::controller(DatabaseSettingController::class)->group(function () {
                    Route::get(DatabaseSetting::VIEW[URI], 'index')->name('db-index');
                });

                Route::prefix('theme')->as('theme.')->controller(ThemeController::class)->group(function () {
                    Route::get(ThemeSetup::VIEW[URI], 'index')->name('setup');
                });
            });

            // ----------- Update Permissions -----------
            Route::middleware('module:system_settings,update')->group(function () {
                Route::controller(BusinessSettingsController::class)->group(function () {
                    Route::post(BusinessSettings::INDEX[URI], 'updateSettings')->name('update');
                    Route::post(BusinessSettings::CLEAR_CACHE[URI], 'clearCache')->name('cache-clear');
                    Route::post(BusinessSettings::APP_SETTINGS[URI], 'updateAppSettings');
                });

                Route::controller(EnvironmentSettingsController::class)->group(function () {
                    Route::post(EnvironmentSettings::VIEW[URI], 'update');
                    Route::post(EnvironmentSettings::FORCE_HTTPS[URI], 'updateForceHttps')->name('environment-https-setup');
                    Route::post(EnvironmentSettings::OPTIMIZE_SYSTEM[URI], 'optimizeSystem')->name('optimize-system');
                    Route::post(EnvironmentSettings::INSTALL_PASSPORT[URI], 'installPassport')->name('install-passport');
                });

                Route::prefix('theme')->as('theme.')->controller(ThemeController::class)->group(function () {
                    Route::post(ThemeSetup::UPLOAD[URI], 'upload')->name('install');
                    Route::post(ThemeSetup::ACTIVE[URI], 'activation')->name('activation');
                    Route::post(ThemeSetup::STATUS[URI], 'publish')->name('publish');
                    Route::post(ThemeSetup::NOTIFY_VENDOR[URI], 'notifyAllTheVendors')->name('notify-all-the-vendors');
                });
            });

            // ----------- Delete Permissions -----------
            Route::middleware('module:system_settings,delete')->group(function () {
                Route::controller(DatabaseSettingController::class)->group(function () {
                    Route::post(DatabaseSetting::DELETE[URI], 'delete')->name('clean-db');
                });

                Route::prefix('theme')->as('theme.')->controller(ThemeController::class)->group(function () {
                    Route::post(ThemeSetup::DELETE[URI], 'delete')->name('delete');
                });
            });
        });

        Route::group(['prefix' => 'vendor-registration-settings', 'as' => 'vendor-registration-settings.'], function () {

            // ----------- Read Permissions -----------
            Route::middleware('module:cms_section,read')->group(function () {
                Route::controller(VendorRegistrationSettingController::class)->group(function () {
                    Route::get(VendorRegistrationSetting::INDEX[URI], 'index')->name('index');
                    Route::get(VendorRegistrationSetting::WITH_US[URI], 'getSellWithUsView')->name('with-us');
                    Route::get(VendorRegistrationSetting::BUSINESS_PROCESS[URI], 'getBusinessProcessView')->name('business-process');
                    Route::get(VendorRegistrationSetting::DOWNLOAD_APP[URI], 'getDownloadAppView')->name('download-app');
                    Route::get(VendorRegistrationSetting::FAQ[URI], 'getFAQView')->name('faq');
                });
            });

            // ----------- Update Permissions -----------
            Route::middleware('module:cms_section,update')->group(function () {
                Route::controller(VendorRegistrationSettingController::class)->group(function () {
                    Route::post(VendorRegistrationSetting::INDEX[URI], 'updateHeaderSection');
                    Route::post(VendorRegistrationSetting::WITH_US[URI], 'updateSellWithUsSection');
                    Route::post(VendorRegistrationSetting::BUSINESS_PROCESS[URI], 'updateBusinessProcess');
                    Route::post(VendorRegistrationSetting::DOWNLOAD_APP[URI], 'updateDownloadAppSection');
                });
            });
        });


        Route::group(['prefix' => 'vendor-registration-reason', 'as' => 'vendor-registration-reason.'], function () {

            // ----------- Read Permissions -----------
            Route::middleware('module:cms_section,read')->group(function () {
                Route::controller(VendorRegistrationReasonController::class)->group(function () {
                    Route::get(VendorRegistrationReason::UPDATE[URI], 'getUpdateView')->name('update');
                });
            });

            // ----------- Create Permissions -----------
            Route::middleware('module:cms_section,create')->group(function () {
                Route::controller(VendorRegistrationReasonController::class)->group(function () {
                    Route::post(VendorRegistrationReason::ADD[URI], 'add')->name('add');
                });
            });

            // ----------- Update Permissions -----------
            Route::middleware('module:cms_section,update')->group(function () {
                Route::controller(VendorRegistrationReasonController::class)->group(function () {
                    Route::post(VendorRegistrationReason::UPDATE[URI], 'update');
                    Route::post(VendorRegistrationReason::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
                });
            });

            // ----------- Delete Permissions -----------
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::controller(VendorRegistrationReasonController::class)->group(function () {
                    Route::post(VendorRegistrationReason::DELETE[URI], 'delete')->name('delete');
                });
            });
        });

        Route::group(['prefix' => 'wholesaler-registration-settings', 'as' => 'wholesaler-registration-settings.'], function () {

            // ----------- Read Permissions -----------
            Route::middleware('module:cms_section,read')->group(function () {
                Route::controller(WholesalerRegistrationSettingController::class)->group(function () {
                    Route::get(WholesalerRegistrationSetting::INDEX[URI], 'index')->name('index');
                    Route::get(WholesalerRegistrationSetting::WITH_US[URI], 'getSellWithUsView')->name('with-us');
                    Route::get(WholesalerRegistrationSetting::BUSINESS_PROCESS[URI], 'getBusinessProcessView')->name('business-process');
                    Route::get(WholesalerRegistrationSetting::DOWNLOAD_APP[URI], 'getDownloadAppView')->name('download-app');
                    Route::get(WholesalerRegistrationSetting::FAQ[URI], 'getFAQView')->name('faq');
                });
            });

            // ----------- Update Permissions -----------
            Route::middleware('module:cms_section,update')->group(function () {
                Route::controller(WholesalerRegistrationSettingController::class)->group(function () {
                    Route::post(WholesalerRegistrationSetting::INDEX[URI], 'updateHeaderSection');
                    Route::post(WholesalerRegistrationSetting::WITH_US[URI], 'updateSellWithUsSection');
                    Route::post(WholesalerRegistrationSetting::BUSINESS_PROCESS[URI], 'updateBusinessProcess');
                    Route::post(WholesalerRegistrationSetting::DOWNLOAD_APP[URI], 'updateDownloadAppSection');
                    Route::post(WholesalerRegistrationSetting::TOGGLE_TYPE_STATUS[URI], 'toggleActiveStatus')->name('toggle-type-status');
                });
            });
        });

        Route::group(['prefix' => 'wholesaler-registration-reason', 'as' => 'wholesaler-registration-reason.'], function () {

            // ----------- Create Permissions -----------
            Route::middleware('module:cms_section,create')->group(function () {
                Route::controller(WholesalerRegistrationReasonController::class)->group(function () {
                    Route::post(WholesalerRegistrationReason::ADD[URI], 'add')->name('add');
                });
            });

            // ----------- Read Permissions -----------
            Route::middleware('module:cms_section,read')->group(function () {
                Route::controller(WholesalerRegistrationReasonController::class)->group(function () {
                    Route::get(WholesalerRegistrationReason::UPDATE[URI], 'getUpdateView')->name('update');
                });
            });

            // ----------- Update Permissions -----------
            Route::middleware('module:cms_section,update')->group(function () {
                Route::controller(WholesalerRegistrationReasonController::class)->group(function () {
                    Route::post(WholesalerRegistrationReason::UPDATE[URI], 'update');
                    Route::post(WholesalerRegistrationReason::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
                });
            });

            // ----------- Delete Permissions -----------
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::controller(WholesalerRegistrationReasonController::class)->group(function () {
                    Route::post(WholesalerRegistrationReason::DELETE[URI], 'delete')->name('delete');
                });
            });
        });
    });

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {

        Route::group(['middleware' => ['module:system_settings']], function () {

            // ----------- Read Permission -----------
            Route::middleware('module:system_settings,read')->group(function () {
                Route::controller(SMSModuleController::class)->group(function () {
                    Route::get(SMSModule::VIEW[URI], 'index')->name('sms-module');
                });
            });

            // ----------- Update Permission -----------
            Route::middleware('module:system_settings,update')->group(function () {
                Route::controller(SMSModuleController::class)->group(function () {
                    Route::put(SMSModule::UPDATE[URI], 'update')->name('addon-sms-set');
                });
            });
        });


        Route::group(['prefix' => 'shipping-method', 'as' => 'shipping-method.', 'middleware' => ['module:system_settings']], function () {

            // ----------- Read Permission -----------
            Route::middleware('module:system_settings,read')->group(function () {
                Route::controller(ShippingMethodController::class)->group(function () {
                    Route::get(ShippingMethod::INDEX[URI], 'index')->name('index');
                    Route::get(ShippingMethod::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                    Route::get(ShippingMethod::AREA_UPDATE[URI] . '/{id}', 'getAreaUpdateView')->name('update-area');
                    Route::get('getStates', 'fGetCountryState')->name('getStates');
                    Route::get('getCities', 'fGetStateCities')->name('getCities');
                    Route::get('getAreas', 'fGetCitiesArea')->name('getAreas');
                });
            });

            // ----------- Create Permission -----------
            Route::middleware('module:system_settings,create')->group(function () {
                Route::controller(ShippingMethodController::class)->group(function () {
                    Route::post(ShippingMethod::INDEX[URI], 'add');
                    Route::post(ShippingMethod::AREA[URI], 'addAreaWiseShipping')->name('add-area');
                });
            });

            // ----------- Update Permission -----------
            Route::middleware('module:system_settings,update')->group(function () {
                Route::controller(ShippingMethodController::class)->group(function () {
                    Route::post(ShippingMethod::UPDATE[URI] . '/{id}', 'update');
                    Route::post(ShippingMethod::AREA_UPDATE[URI] . '/{id}', 'updateArea');
                    Route::post(ShippingMethod::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
                    Route::post(ShippingMethod::UPDATE_AREA_STATUS[URI], 'updateAreaStatus')->name('update-area-status');
                    Route::post(ShippingMethod::UPDATE_SHIPPING_RESPONSIBILITY[URI], 'updateShippingResponsibility')->name('update-shipping-responsibility');
                });
            });

            // ----------- Delete Permission -----------
            Route::middleware('module:system_settings,delete')->group(function () {
                Route::controller(ShippingMethodController::class)->group(function () {
                    Route::post(ShippingMethod::DELETE[URI], 'delete')->name('delete');
                    Route::post(ShippingMethod::AREA_DELETE[URI], 'deleteArea')->name('delete-area');
                });
            });
        });


        Route::group(['prefix' => 'shipping-type', 'as' => 'shipping-type.', 'middleware' => ['module:system_settings']], function () {

            // Create or Update permission
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post(ShippingType::INDEX[URI], [ShippingTypeController::class, 'addOrUpdate'])->name('index');
            });
        });


        Route::group(['prefix' => 'category-shipping-cost', 'as' => 'category-shipping-cost.', 'middleware' => ['module:system_settings']], function () {

            // Store permission
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post('store', [CategoryShippingCostController::class, 'add'])->name('store');
            });
        });
        Route::get('ucm', [UcmConfigController::class, 'index'])->name('ucm');
        Route::post('ucm/update', [UcmConfigController::class, 'update'])->name('ucm.update');

        Route::group(['prefix' => 'mail', 'as' => 'mail.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(Mail::VIEW[URI], [MailController::class, 'index'])->name('index');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(Mail::UPDATE[URI], [MailController::class, 'update'])->name('update');
                Route::post(Mail::UPDATE_SENDGRID[URI], [MailController::class, 'updateSendGrid'])->name('update-sendgrid');
            });

            // Send permission - you can treat as create or custom permission
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post(Mail::SEND[URI], [MailController::class, 'send'])->name('send');
            });
        });


        Route::group(['prefix' => 'order-settings', 'as' => 'order-settings.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(BusinessSettings::ORDER_VIEW[URI], [OrderSettingsController::class, 'index'])->name('index');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(BusinessSettings::ORDER_UPDATE[URI], [OrderSettingsController::class, 'update'])->name('update-order-settings');
            });
        });


        Route::group(['prefix' => 'vendor-settings', 'as' => 'vendor-settings.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(BusinessSettings::VENDOR_VIEW[URI], [VendorSettingsController::class, 'index'])->name('index');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(BusinessSettings::VENDOR_SETTINGS_UPDATE[URI], [VendorSettingsController::class, 'update'])->name('update-vendor-settings');
            });
        });


        Route::group(['prefix' => 'delivery-man-settings', 'as' => 'delivery-man-settings.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(BusinessSettings::DELIVERYMAN_VIEW[URI], [DeliverymanSettingsController::class, 'index'])->name('index');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(BusinessSettings::DELIVERYMAN_VIEW_UPDATE[URI], [DeliverymanSettingsController::class, 'update'])->name('update');
            });
        });

        Route::group(['prefix' => 'payment-method', 'as' => 'payment-method.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(PaymentMethod::LIST[URI], [PaymentMethodController::class, 'index'])->name('index');
                Route::get(PaymentMethod::PAYMENT_OPTION[URI], [PaymentMethodController::class, 'getPaymentOptionView'])->name('payment-option');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(PaymentMethod::PAYMENT_OPTION[URI], [PaymentMethodController::class, 'updatePaymentOption']);
                Route::put(PaymentMethod::UPDATE_CONFIG[URI], [PaymentMethodController::class, 'UpdatePaymentConfig'])->name('addon-payment-set');
            });
        });


        Route::group(['prefix' => 'offline-payment-method', 'as' => 'offline-payment-method.', 'middleware' => ['module:system_settings']], function () {
            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(OfflinePaymentMethod::INDEX[URI], [OfflinePaymentMethodController::class, 'index'])->name('index');
                Route::get(OfflinePaymentMethod::ADD[URI], [OfflinePaymentMethodController::class, 'getAddView'])->name('add');
                Route::get(OfflinePaymentMethod::UPDATE[URI] . '/{id}', [OfflinePaymentMethodController::class, 'getUpdateView'])->name('update');
            });

            // Create permission
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post(OfflinePaymentMethod::ADD[URI], [OfflinePaymentMethodController::class, 'add']);
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(OfflinePaymentMethod::UPDATE[URI] . '/{id}', [OfflinePaymentMethodController::class, 'update']);
                Route::post(OfflinePaymentMethod::UPDATE_STATUS[URI], [OfflinePaymentMethodController::class, 'updateStatus'])->name('update-status');
            });

            // Delete permission
            Route::middleware('module:system_settings,delete')->group(function () {
                Route::post(OfflinePaymentMethod::DELETE[URI], [OfflinePaymentMethodController::class, 'delete'])->name('delete');
            });
        });

        Route::group(['prefix' => 'delivery-restriction', 'as' => 'delivery-restriction.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(DeliveryRestriction::VIEW[URI], [DeliveryRestrictionController::class, 'index'])->name('index');
            });

            // Create permission
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post(DeliveryRestriction::ADD[URI], [DeliveryRestrictionController::class, 'add'])->name('add-delivery-country');
                Route::post(DeliveryRestriction::STATE_ADD[URI], [DeliveryRestrictionController::class, 'addState'])->name('add-delivery-state');
                Route::post(DeliveryRestriction::CITY_ADD[URI], [DeliveryRestrictionController::class, 'addCity'])->name('add-delivery-city');
                Route::post(DeliveryRestriction::ZIPCODE_ADD[URI], [DeliveryRestrictionController::class, 'addZipCode'])->name('add-zip-code');
                Route::post(DeliveryRestriction::AREA_ADD[URI], [DeliveryRestrictionController::class, 'addArea'])->name('add-area');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(DeliveryRestriction::COUNTRY_RESTRICTION[URI], [DeliveryRestrictionController::class, 'countryRestrictionStatusChange'])->name('country-restriction-status-change');
                Route::post(DeliveryRestriction::STATE_RESTRICTION[URI], [DeliveryRestrictionController::class, 'stateRestrictionStatusChange'])->name('state-restriction-status-change');
                Route::post(DeliveryRestriction::CITY_RESTRICTION[URI], [DeliveryRestrictionController::class, 'cityRestrictionStatusChange'])->name('city-restriction-status-change');
                Route::post(DeliveryRestriction::ZIPCODE_RESTRICTION[URI], [DeliveryRestrictionController::class, 'zipcodeRestrictionStatusChange'])->name('zipcode-restriction-status-change');
                Route::post(DeliveryRestriction::AREA_RESTRICTION[URI], [DeliveryRestrictionController::class, 'areaRestrictionStatusChange'])->name('area-restriction-status-change');
            });

            // Delete permission
            Route::middleware('module:system_settings,delete')->group(function () {
                Route::delete(DeliveryRestriction::DELETE[URI], [DeliveryRestrictionController::class, 'delete'])->name('delivery-country-delete');
                Route::delete(DeliveryRestriction::STATE_DELETE[URI], [DeliveryRestrictionController::class, 'deleteState'])->name('delivery-state-delete');
                Route::delete(DeliveryRestriction::CITY_DELETE[URI], [DeliveryRestrictionController::class, 'deleteCity'])->name('delivery-city-delete');
                Route::delete(DeliveryRestriction::ZIPCODE_DELETE[URI], [DeliveryRestrictionController::class, 'deleteZipCode'])->name('zip-code-delete');
                Route::delete(DeliveryRestriction::AREA_DELETE[URI], [DeliveryRestrictionController::class, 'deleteArea'])->name('area-delete');
            });
        });


        Route::group(['prefix' => 'email-templates', 'as' => 'email-templates.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get('index', [EmailTemplatesController::class, 'index'])->name('index');
                Route::get(EmailTemplate::VIEW[URI] . '/{type}/{tab}', [EmailTemplatesController::class, 'getView'])->name('view');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(EmailTemplate::UPDATE[URI] . '/{type}/{tab}', [EmailTemplatesController::class, 'update'])->name('update');
                Route::post(EmailTemplate::UPDATE_STATUS[URI] . '/{type}/{tab}', [EmailTemplatesController::class, 'updateStatus'])->name('update-status');
            });
        });


        Route::group(['prefix' => 'priority-setup', 'as' => 'priority-setup.', 'middleware' => ['module:system_settings']], function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(PrioritySetup::INDEX[URI], [PrioritySetupController::class, 'index'])->name('index');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(PrioritySetup::INDEX[URI], [PrioritySetupController::class, 'update']);
            });
        });
    });

    Route::group(['prefix' => 'system-settings', 'as' => 'system-settings.', 'middleware' => ['module:system_settings']], function () {

        // Read permission
        Route::middleware('module:system_settings,read')->group(function () {
            Route::get(SoftwareUpdate::VIEW[URI], [SoftwareUpdateController::class, 'index'])->name('software-update');
        });

        // Update permission
        Route::middleware('module:system_settings,update')->group(function () {
            Route::post(SoftwareUpdate::VIEW[URI], [SoftwareUpdateController::class, 'update']);
        });
    });


    Route::group(['prefix' => 'currency', 'as' => 'currency.', 'middleware' => ['module:system_settings']], function () {

        // Read permission
        Route::middleware('module:system_settings,read')->group(function () {
            Route::get(Currency::LIST[URI], [CurrencyController::class, 'index'])->name('view');
            Route::get(Currency::UPDATE[URI] . '/{id}', [CurrencyController::class, 'getUpdateView'])->name('update');
        });

        // Create permission
        Route::middleware('module:system_settings,create')->group(function () {
            Route::post(Currency::ADD[URI], [CurrencyController::class, 'add'])->name('store');
        });

        // Update permission
        Route::middleware('module:system_settings,update')->group(function () {
            Route::post(Currency::UPDATE[URI] . '/{id}', [CurrencyController::class, 'update']);
            Route::post(Currency::DEFAULT[URI], [CurrencyController::class, 'updateSystemCurrency'])->name('system-currency-update');
            Route::post(Currency::STATUS[URI], [CurrencyController::class, 'status'])->name('status');
        });

        // Delete permission
        Route::middleware('module:system_settings,delete')->group(function () {
            Route::post(Currency::DELETE[URI], [CurrencyController::class, 'delete'])->name('delete');
        });
    });


    Route::group(['prefix' => 'addon', 'as' => 'addon.', 'middleware' => ['module:system_settings']], function () {

        // Read permission
        Route::middleware('module:system_settings,read')->group(function () {
            Route::get(AddonSetup::VIEW[URI], [AddonController::class, 'index'])->name('index');
        });

        // Create permission
        Route::middleware('module:system_settings,create')->group(function () {
            Route::post(AddonSetup::UPLOAD[URI], [AddonController::class, 'upload'])->name('upload');
        });

        // Update permission
        Route::middleware('module:system_settings,update')->group(function () {
            Route::post(AddonSetup::PUBLISH[URI], [AddonController::class, 'publish'])->name('publish');
            Route::post(AddonSetup::ACTIVATION[URI], [AddonController::class, 'activation'])->name('activation');
        });

        // Delete permission
        Route::middleware('module:system_settings,delete')->group(function () {
            Route::post(AddonSetup::DELETE[URI], [AddonController::class, 'delete'])->name('delete');
        });
    });


    // Social Login Routes
    Route::group(['prefix' => 'social-login', 'as' => 'social-login.', 'middleware' => ['module:system_settings']], function () {

        // Read permission
        Route::middleware('module:system_settings,read')->group(function () {
            Route::get(SocialLoginSettings::VIEW[URI], [SocialLoginSettingsController::class, 'index'])->name('view');
        });

        // Update permission
        Route::middleware('module:system_settings,update')->group(function () {
            Route::post(SocialLoginSettings::UPDATE[URI] . '/{service}', [SocialLoginSettingsController::class, 'update'])->name('update');
            Route::post(SocialLoginSettings::APPLE_UPDATE[URI] . '/{service}', [SocialLoginSettingsController::class, 'updateAppleLogin'])->name('update-apple');
        });
    });

    // Storage Connection Settings Routes
    Route::group(['prefix' => 'storage-connection-settings', 'as' => 'storage-connection-settings.', 'middleware' => ['module:system_settings']], function () {

        // Read permission
        Route::middleware('module:system_settings,read')->group(function () {
            Route::get(StorageConnectionSettings::INDEX[URI], [StorageConnectionSettingsController::class, 'index'])->name('index');
        });

        // Update permission
        Route::middleware('module:system_settings,update')->group(function () {
            Route::post(StorageConnectionSettings::STORAGE_TYPE[URI], [StorageConnectionSettingsController::class, 'updateStorageType'])->name('update-storage-type');
            Route::post(StorageConnectionSettings::S3_STORAGE_CREDENTIAL[URI], [StorageConnectionSettingsController::class, 'updateS3Credential'])->name('s3-credential');
        });
    });

    // Firebase OTP Verification Routes
    Route::group(['prefix' => 'firebase-otp-verification', 'as' => 'firebase-otp-verification.', 'middleware' => ['module:system_settings']], function () {

        // Read permission
        Route::middleware('module:system_settings,read')->group(function () {
            Route::get(FirebaseOTPVerification::INDEX[URI], [FirebaseOTPVerificationController::class, 'index'])->name('index');
        });

        // Update permission
        Route::middleware('module:system_settings,update')->group(function () {
            Route::post(FirebaseOTPVerification::UPDATE[URI], [FirebaseOTPVerificationController::class, 'updateConfig'])->name('update');
            Route::post(FirebaseOTPVerification::FIREBASE_CONFIG_VALIDATION[URI], [FirebaseOTPVerificationController::class, 'getConfigValidation'])->name('config-status-validation');
        });
    });

    // Social Media Chat Routes
    Route::group(['prefix' => 'social-media-chat', 'as' => 'social-media-chat.', 'middleware' => ['module:system_settings']], function () {

        // Read permission
        Route::middleware('module:system_settings,read')->group(function () {
            Route::get(SocialMediaChat::VIEW[URI], [SocialMediaChatController::class, 'index'])->name('view');
        });

        // Update permission
        Route::middleware('module:system_settings,update')->group(function () {
            Route::post(SocialMediaChat::UPDATE[URI] . '/{service}', [SocialMediaChatController::class, 'update'])->name('update');
        });
    });


    Route::group(['prefix' => 'product-settings', 'as' => 'product-settings.', 'middleware' => ['module:system_settings']], function () {

        // Business Settings Controller
        Route::controller(BusinessSettingsController::class)->group(function () {
            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(BusinessSettings::PRODUCT_SETTINGS[URI], 'getProductSettingsView')->name('index');
            });
            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(BusinessSettings::PRODUCT_SETTINGS[URI], 'updateProductSettings');
            });
        });

        // Inhouse Shop Controller
        Route::controller(InhouseShopController::class)->group(function () {
            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(InhouseShop::VIEW[URI], 'index')->name('inhouse-shop');
            });
            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(InhouseShop::VIEW[URI], 'update');
                Route::post(InhouseShop::TEMPORARY_CLOSE[URI], 'getTemporaryClose')->name('inhouse-shop-temporary-close');
                Route::post(InhouseShop::VACATION_ADD[URI], 'addVacation')->name('vacation-add');
            });
        });
    });

    Route::group(['prefix' => 'warranty-settings', 'as' => 'warranty-settings.', 'middleware' => ['module:system_settings']], function () {

        // Business Settings Controller
        Route::controller(BusinessSettingsController::class)->group(function () {
            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get('/', 'getWarrantySettingsView')->name('index');
            });
            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post('/', 'updateWarrantySettings');
            });
        });
    });


    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.', 'middleware' => ['module:promotion_management']], function () {

        Route::controller(BusinessSettingsController::class)->group(function () {
            Route::middleware('module:promotion_management,announcement')->group(function () {
                Route::get(BusinessSettings::ANNOUNCEMENT[URI], 'getAnnouncementView')->name('announcement');
            });
            Route::middleware('module:promotion_management,announcement')->group(function () {
                Route::post(BusinessSettings::ANNOUNCEMENT[URI], 'updateAnnouncement');
            });
        });
    });


    Route::group(['prefix' => 'seo-settings', 'as' => 'seo-settings.', 'middleware' => ['module:system_settings']], function () {

        Route::controller(SEOSettingsController::class)->group(function () {
            // Read permission (view pages)
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(SEOSettings::WEB_MASTER_TOOL[URI], 'index')->name('web-master-tool');
                Route::get(SEOSettings::ROBOT_TXT[URI], 'getRobotTxtView')->name('robot-txt');
            });

            // Update permission (post updates)
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(SEOSettings::WEB_MASTER_TOOL[URI], 'updateWebMasterTool');
                Route::post(SEOSettings::ROBOT_TXT[URI], 'updateRobotText');
            });
        });

        Route::group(['prefix' => 'robots-meta-content', 'as' => 'robots-meta-content.'], function () {
            Route::controller(RobotsMetaContentController::class)->group(function () {
                // Read
                Route::middleware('module:system_settings,read')->group(function () {
                    Route::get(RobotsMetaContent::ROBOTS_META_CONTENT[URI], 'index')->name('index');
                    Route::get(RobotsMetaContent::DELETE_PAGE[URI], 'getPageDelete')->name('delete-page');
                    Route::get(RobotsMetaContent::PAGE_CONTENT_VIEW[URI], 'getPageAddContentView')->name('page-content-view');
                });

                // Create
                Route::middleware('module:system_settings,create')->group(function () {
                    Route::post(RobotsMetaContent::ADD_PAGE[URI], 'addPage')->name('add-page');
                });

                // Update
                Route::middleware('module:system_settings,update')->group(function () {
                    Route::post(RobotsMetaContent::PAGE_CONTENT_UPDATE[URI], 'getPageContentUpdate')->name('page-content-update');
                });

                // Delete (assuming delete page is a GET; if POST, adjust accordingly)
                Route::middleware('module:system_settings,delete')->group(function () {
                    Route::get(RobotsMetaContent::DELETE_PAGE[URI], 'getPageDelete')->name('delete-page');
                });
            });
        });

        Route::controller(SiteMapController::class)->group(function () {
            // Read (view sitemap)
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(SiteMap::SITEMAP[URI], 'index')->name('sitemap');
                Route::get(SiteMap::GENERATE_AND_DOWNLOAD[URI], 'getGenerateAndDownload')->name('sitemap-generate-download');
                Route::get(SiteMap::GENERATE_AND_UPLOAD[URI], 'getGenerateAndUpload')->name('sitemap-generate-upload');
                Route::get(SiteMap::DOWNLOAD[URI], 'getDownload')->name('sitemap-download');
                Route::get(SiteMap::DELETE[URI], 'getDelete')->name('sitemap-delete');
            });

            // Create/Update (upload sitemap)
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post(SiteMap::UPLOAD[URI], 'getUpload')->name('sitemap-manual-upload');
            });
        });
    });

    Route::group(['prefix' => 'error-logs', 'as' => 'error-logs.'], function () {
        Route::controller(ErrorLogsController::class)->group(function () {

            // Read routes
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(ErrorLogs::INDEX[URI], 'index')->name('index');
                // Agar single log show method ho to wo bhi yahan dal sakte hain
                // Route::get(ErrorLogs::INDEX[URI] . '/{id}', 'show')->name('show');
            });

            // Update routes
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(ErrorLogs::INDEX[URI], 'update')->name('update');
            });

            // Delete routes
            Route::middleware('module:system_settings,delete')->group(function () {
                Route::delete(ErrorLogs::INDEX[URI], 'delete')->name('delete');
                Route::delete(ErrorLogs::DELETE_SELECTED_ERROR_LOGS[URI], 'deleteSelectedErrorLogs')->name('delete-selected-error-logs');
            });
        });
    });


    Route::group(['prefix' => 'file-manager', 'as' => 'file-manager.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(FileManagerController::class)->group(function () {

            // Read permissions
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(FileManager::VIEW[URI] . '/{folderPath?}', 'getFoldersView')->name('index');
                Route::get(FileManager::DOWNLOAD[URI] . '/{file_name}', 'download')->name('download');
            });

            // Create/Update permissions
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post(FileManager::IMAGE_UPLOAD[URI], 'upload')->name('image-upload');
            });
        });
    });

    Route::group(['prefix' => 'helpTopic', 'as' => 'helpTopic.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(HelpTopicController::class)->group(function () {

            // Read permission
            Route::middleware('module:system_settings,read')->group(function () {
                Route::get(HelpTopic::LIST[URI], 'index')->name('list');
                Route::get(HelpTopic::STATUS[URI] . '/{id}', 'updateStatus')->name('status');
                Route::get(HelpTopic::UPDATE[URI] . '/{id}', 'getUpdateResponse')->name('update');
            });

            // Create permission
            Route::middleware('module:system_settings,create')->group(function () {
                Route::post(HelpTopic::ADD[URI], 'add')->name('add-new');
            });

            // Update permission
            Route::middleware('module:system_settings,update')->group(function () {
                Route::post(HelpTopic::UPDATE[URI] . '/{id}', 'update')->name('update');
            });

            // Delete permission
            Route::middleware('module:system_settings,delete')->group(function () {
                Route::post(HelpTopic::DELETE[URI], 'delete')->name('delete');
            });
        });
    });


    Route::group(['prefix' => 'refund-section/refund', 'as' => 'refund-section.refund.', 'middleware' => ['module:order_management']], function () {
        Route::controller(RefundController::class)->group(function () {

            Route::get(RefundRequest::LIST[URI] . '/{status}', 'index')->middleware('module:order_management,refund_request_list')->name('list');
            Route::get(RefundRequest::EXPORT[URI] . '/{status}', 'exportList')->middleware('module:order_management,refund_request_list')->name('export');
            Route::get(RefundRequest::DETAILS[URI] . '/{id}', 'getDetailsView')->middleware('module:order_management,refund_request_view')->name('details');
            Route::post(RefundRequest::UPDATE_STATUS[URI], 'updateRefundStatus')->middleware('module:order_management,refund_request_update')->name('refund-status-update');
        });
    });

    Route::group(['prefix' => 'address/order', 'as' => 'address.order.', 'middleware' => ['module:order_management']], function () {
        Route::get('/get-states', [ShippingAjaxController::class, 'getStates'])->name('get.states');
        Route::get('/get-cities', [ShippingAjaxController::class, 'getCities'])->name('get.cities');
        Route::get('/get-areas', [ShippingAjaxController::class, 'getAreas'])->name('get.areas');
        Route::get('/get-billing-areas', [ShippingAjaxController::class, 'getBillingAreas'])->name('get.billing.areas');
    });


    Route::prefix('branch')->name('branch.')->middleware(['module:branch_management'])->group(function () {

        // Read-only routes
        Route::middleware('module:branch_management,read')->group(function () {
            Route::get('/', [BranchController::class, 'index'])->name('index');
            Route::get('/product-inventory', [ProductInventoryController::class, 'productInventory'])->name('product-inventory');
            Route::get('sales-tracking', [ProductInventoryController::class, 'totelSale'])->name('product-sells');
            Route::get('/stock-movement', [BranchController::class, 'stockMovement'])->name('stock-movement');
            Route::get('/stock-updates', [BranchController::class, 'stockUpdates'])->name('stock-updates');
            Route::get('/stock-alerts', [BranchController::class, 'stockAlerts'])->name('alerts');
            Route::get('/received', [StockMovementController::class, 'received'])->name('stock.received');
            Route::get('/request', [StockMovementController::class, 'request'])->name('stock.request');
            Route::get('/approve', [StockMovementController::class, 'approveIndex'])->name('stock.approvelist');
            Route::get('/support', [BranchController::class, 'support'])->name('support');
            Route::get('/vendors', [ManageBranchController::class, 'vendors'])->name('vendors');
            Route::get('vendors/{id}', [ManageBranchController::class, 'show'])->name('vendors.view');
        });

        // Create routes
        Route::middleware('module:branch_management,create')->group(function () {
            Route::post('/stock/store', [StockMovementController::class, 'saveStockRequest'])->name('stock.request.store');
        });

        // Update routes
        Route::middleware('module:branch_management,update')->group(function () {
            Route::post('approve/{id}', [StockMovementController::class, 'approveProduct'])->name('stock.approve');
            Route::post('reject/{id}', [StockMovementController::class, 'rejectProduct'])->name('stock.reject');
        });
    });




    Route::prefix('content-management')->name('content-management.')->group(function () {

        // Home Route
        Route::middleware(['module:cms_section'])->group(function () {

            // READ routes
            Route::middleware('module:cms_section,read')->group(function () {
                Route::get('home', [HomeController::class, 'index'])->name('home');
                Route::get('home/edit', [HomeController::class, 'edit'])->name('home.edit');
            });

            // CREATE routes
            Route::middleware('module:cms_section,create')->group(function () {
                Route::post('home/client-review/store', [HomeController::class, 'storeClientReview'])->name('client-review.store');
                Route::post('/faqs/add', [HomeController::class, 'addFaq'])->name('faqs.add');
                Route::post('banner/store', [HomeController::class, 'storeBanner'])->name('banner.store');
            });

            Route::middleware('module:cms_section,update')->group(function () {
                Route::put('home/trusted-by/update/{index}', [HomeController::class, 'updateTrustedBy'])->name('trusted_by.update');
                Route::put('home/products/update/{index}', [HomeController::class, 'updateProducts'])->name('Products.update');
                Route::put('home/client-review/update', [HomeController::class, 'updateClientReview'])->name('client_review.update');
                Route::put('/admin/cards/update', [HomeController::class, 'updateWhyChoose'])->name('why-choose.update');
                Route::post('why-join-us/update', [HomeController::class, 'updateWhyJoinUs'])->name('why_join_us.update');
                Route::put('wholesaler-section/update', [HomeController::class, 'updateWholesalerSection'])->name('wholesaler_section.update');
                Route::put('find-perfect-match/update', [HomeController::class, 'updateFindPerfectMatch'])->name('find_perfect_match.update');
                Route::post('/faqs/update', [HomeController::class, 'updateFaq'])->name('faqs.update');
                Route::post('download-app/update', [HomeController::class, 'updateDownloadAppItem'])->name('download-app.update');
                Route::put('download-app/heading/update', [HomeController::class, 'updateDownloadAppHeading'])->name('download-app.heading.update');
                Route::put('why-choose/heading/update', [HomeController::class, 'updateWhyChooseHeading'])->name('why-choose.heading.update');
                Route::put('why-join/heading/update', [HomeController::class, 'updateWhyJoinHeading'])->name('why_join_us.heading.update');
                Route::put('category/update', [HomeController::class, 'updateCategory'])->name('category.update');
                Route::put('cms/blog/update', [HomeController::class, 'updateBlog'])->name('cms.blog.update');
                Route::post('banner/update', [HomeController::class, 'updateBanner'])->name('banner.update');
                Route::post('banner/toggle-status', [HomeController::class, 'toggleStatusBanner'])->name('banner.toggle-status');
                Route::post('home/section/toggle-status', [HomeController::class, 'toggleStatusSection'])->name('section.toggle-status');
            });

            // DELETE routes
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::delete('home/client-review/delete', [HomeController::class, 'deleteClientReview'])->name('client_review.delete');
                Route::delete('/admin/cards/delete', [HomeController::class, 'deleteWhyChoose'])->name('why-choose.delete');
                Route::delete('/faqs/delete', [HomeController::class, 'deleteFaq'])->name('faqs.delete');
                Route::post('banner/delete', [HomeController::class, 'deleteBanner'])->name('banner.delete');
            });





            // Blog Route
            Route::middleware('module:cms_section,read')->group(function () {
                Route::get('blog', [BlogController::class, 'index'])->name('blog');
                Route::get('blog/edit/{id}', [BlogController::class, 'edit'])->name('blog.edit');
                Route::get('blog/create', [BlogController::class, 'create'])->name('blog.create');
            });

            // CREATE routes
            Route::middleware('module:cms_section,create')->group(function () {
                Route::post('blog/store', [BlogController::class, 'store'])->name('blog.store');
            });

            // UPDATE routes
            Route::middleware('module:cms_section,update')->group(function () {
                Route::put('blog/update/{id}', [BlogController::class, 'update'])->name('blog.update');
                Route::post('blog/status/{id}', [BlogController::class, 'toggleStatus'])->name('blog.status');
            });

            // DELETE routes
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::delete('blog/delete/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');
            });

            Route::middleware('module:cms_section,read')->group(function () {
                Route::get('products', [ProductCmsController::class, 'index'])->name('products');
                Route::get('products/edit/{id}', [ProductCmsController::class, 'edit'])->name('products.edit');
                Route::get('products/create', [ProductCmsController::class, 'create'])->name('products.create');
            });

            // CREATE routes
            Route::middleware('module:cms_section,create')->group(function () {
                Route::post('products/store', [ProductCmsController::class, 'store'])->name('products.store');
            });

            // UPDATE routes
            Route::middleware('module:cms_section,update')->group(function () {
                Route::put('products/update/{id}', [ProductCmsController::class, 'update'])->name('products.update');
                Route::post('products/status', [ProductCmsController::class, 'toggleStatus'])->name('products.status');
            });

            // DELETE routes
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::delete('products/delete/{id}', [ProductCmsController::class, 'destroy'])->name('products.destroy');
            });


            Route::middleware('module:cms_section,read')->group(function () {
                Route::get('services', [ServiceCmsController::class, 'index'])->name('services');
                Route::get('services/edit/{id}', [ServiceCmsController::class, 'edit'])->name('services.edit');
                Route::get('services/create', [ServiceCmsController::class, 'create'])->name('services.create');
            });

            // CREATE routes
            Route::middleware('module:cms_section,create')->group(function () {
                Route::post('services/store', [ServiceCmsController::class, 'store'])->name('services.store');
            });

            // UPDATE routes
            Route::middleware('module:cms_section,update')->group(function () {
                Route::put('services/update/{id}', [ServiceCmsController::class, 'update'])->name('services.update');
                Route::post('services/status', [ServiceCmsController::class, 'toggleStatus'])->name('services.status');
            });

            // DELETE routes
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::delete('services/delete/{id}', [ServiceCmsController::class, 'destroy'])->name('services.destroy');
            });
            Route::middleware('module:cms_section,read')->group(function () {
                Route::get('about-us', [AboutController::class, 'index'])->name('about-us');
                Route::get('about-us/page/{section}', [AboutController::class, 'pages'])->name('about-us.pages');
                Route::get('about-us/create', [AboutController::class, 'create'])->name('about-us.create');
                Route::get('about-us/edit/{section}/{id}', [AboutController::class, 'edit'])->name('about-us.edit');
            });

            // CREATE
            Route::middleware('module:cms_section,create')->group(function () {
                Route::post('about-us/store/{section}', [AboutController::class, 'store'])->name('about-us.store');
            });

            // UPDATE
            Route::middleware('module:cms_section,update')->group(function () {
                Route::put('about-us/update/{section}/{id}', [AboutController::class, 'update'])->name('about-us.update');
                Route::post('about-us/toggle-status', [AboutController::class, 'toggleStatus'])->name('about-us.toggle-status');
            });

            // DELETE
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::delete('about-us/destroy/{section}/{id}', [AboutController::class, 'destroy'])->name('about-us.destroy');
            });


            Route::middleware('module:cms_section,read')->group(function () {
                Route::get('career', [CareerController::class, 'index'])->name('career');
                Route::get('career/page/{section}', [CareerController::class, 'pages'])->name('career.pages');
                Route::get('career/edit/{section}/{id}', [CareerController::class, 'edit'])->name('career.edit');
                Route::get('career/create', [CareerController::class, 'create'])->name('career.create');
            });

            // CREATE
            Route::middleware('module:cms_section,create')->group(function () {
                Route::post('career/store/{section}', [CareerController::class, 'store'])->name('career.store');
            });

            // UPDATE
            Route::middleware('module:cms_section,update')->group(function () {
                Route::put('career/update/{section}/{id}', [CareerController::class, 'update'])->name('career.update');
                Route::post('career/status', [CareerController::class, 'toggleStatus'])->name('career.toggle-status');
            });

            // DELETE
            Route::middleware('module:cms_section,delete')->group(function () {
                Route::delete('career/delete/{section}/{id}', [CareerController::class, 'destroy'])->name('career.destroy');
            });
            Route::middleware('module:cms_section,read')->group(function () {
                Route::get('contact-us', [ContactUsController::class, 'index'])->name('contact-us');
                Route::get('refund-policy', [RefundPolicyController::class, 'index'])->name('refund-policy');
                Route::get('return-policy', [ReturnPolicyController::class, 'index'])->name('return-policy');
                Route::get('cancellation-policy', [CancellationPolicyController::class, 'index'])->name('cancellation-policy');
            });

            Route::middleware('module:cms_section,update')->group(function () {
                Route::get('contact-us/edit', [ContactUsController::class, 'edit'])->name('contact-us.edit');
                Route::get('refund-policy/edit', [RefundPolicyController::class, 'edit'])->name('refund-policy.edit');
                Route::get('return-policy/edit', [ReturnPolicyController::class, 'edit'])->name('return-policy.edit');
                Route::get('cancellation-policy/edit', [CancellationPolicyController::class, 'edit'])->name('cancellation-policy.edit');
            });
        });
    });
});

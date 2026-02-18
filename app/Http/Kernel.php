<?php

namespace App\Http;

use App\Http\Middleware\CheckLicense;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DeliveryManAuth;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Middleware\APIGuestMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\CustomerIsActiveCheck;
use App\Http\Middleware\InstallationMiddleware;
use App\Http\Middleware\SellerApiAuthMiddleware;
use App\Http\Middleware\APILocalizationMiddleware;
use App\Http\Middleware\DatabaseRefreshMiddleware;
use App\Http\Middleware\MaintenanceModeMiddleware;
use App\Http\Middleware\ModulePermissionMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;


class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        DatabaseRefreshMiddleware::class,
        \App\Http\Middleware\CheckLicense::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\Localization::class

        ],

        'api' => [
            'throttle:3000,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => AdminMiddleware::class,
        'seller' => SellerMiddleware::class,
        'customer' => CustomerMiddleware::class,
        'module' => ModulePermissionMiddleware::class,
        'api_lang' => APILocalizationMiddleware::class,
        'maintenance_mode' => MaintenanceModeMiddleware::class,
        'delivery_man_auth' => DeliveryManAuth::class,
        'seller_api_auth' => SellerApiAuthMiddleware::class,
        'guestCheck' => GuestMiddleware::class,
        'apiGuestCheck' => APIGuestMiddleware::class,
        'license' => CheckLicense::class,
    ];


    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inbox:generate-suggestions')->twiceDaily(10, 14); // 10AM, 2PM
        $schedule->call(function () {
            $slaService = app(\App\Services\SlaService::class);

            $entities = [];
            $entities = array_merge($entities, \App\Models\SupportTicket::whereNotIn('status', ['closed', 'resolved', 'rejected', 'hired'])->get()->toArray());
            $entities = array_merge($entities, \App\Models\InboxMessage::whereNotIn('status', ['closed', 'resolved'])->get()->toArray());
            $entities = array_merge($entities, \App\Models\Lead::whereNotIn('status', ['converted', 'closed'])->get()->toArray());
            $entities = array_merge($entities, \App\Models\Deal::whereNotIn('status', ['won', 'lost'])->get()->toArray());

            foreach ($entities as $entity) {
                $slaService->checkForBreaches($entity);
            }
        })->everyFiveMinutes(); 
    }
}

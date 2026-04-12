<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\AutoExpireWarranties::class,
        \App\Console\Commands\ExpireServiceInvoicePaymentLinks::class,
        \App\Console\Commands\ExpireWarrantyClaimPaymentLinks::class,
        \App\Console\Commands\AuditMissingAdminRoles::class,
        \App\Console\Commands\InventoryReconcileCommand::class,
        \App\Console\Commands\SeedUiTranslationEntries::class,
        \App\Console\Commands\SystemVerifyCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('warranties:expire')->daily();
        $schedule->command('service-invoices:expire-pending-links')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('warranty-claims:expire-pending-links')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('sla:check-breaches')->everyTenMinutes();
        $schedule->command('sla:start-for-new')->everyMinute();
        $schedule->command('inventory:reconcile --dry-run')->dailyAt('02:30');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

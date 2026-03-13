<?php

namespace App\Providers;

use App\Models\Warranty;
use App\Policies\WarrantyPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Warranty::class => WarrantyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            if ($user instanceof \App\Models\Admin && $user->isSuperAdmin()) {
                return true;
            }

            return null;
        });
    }
}

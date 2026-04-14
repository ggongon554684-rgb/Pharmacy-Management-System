<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Patient;
use App\Models\Prescriber;
use App\Models\Prescription;
use App\Models\users;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Sale;
use App\Observers\AuditLogObserver;
use Illuminate\Pagination\Paginator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Product::observe(AuditLogObserver::class);
        InventoryBatch::observe(AuditLogObserver::class);
        Patient::observe(AuditLogObserver::class);
        Prescription::observe(AuditLogObserver::class);
        Sale::observe(AuditLogObserver::class);
    }
}

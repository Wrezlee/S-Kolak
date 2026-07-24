<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pakai tampilan pagination custom (Showing X to Y of Z entries + first/last)
        // di seluruh halaman yang memanggil {{ $items->links() }}.
        Paginator::defaultView('vendor.pagination.custom');
    }
}
<?php

namespace App\Providers;

use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\EloquentInvoiceRepository;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\InvoiceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(InvoiceServiceInterface::class, InvoiceService::class);
    }

    public function boot(): void
    {
        //
    }
}

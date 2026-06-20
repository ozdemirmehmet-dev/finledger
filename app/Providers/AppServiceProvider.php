<?php

namespace App\Providers;

use App\Adapters\Tax\Contracts\TaxCalculatorInterface;
use App\Adapters\Tax\TurkeyTaxAdapter;
use App\Adapters\Tax\UKTaxAdapter;
use App\Adapters\Tax\USTaxAdapter;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\ReceiptRepositoryInterface;
use App\Repositories\EloquentInvoiceRepository;
use App\Repositories\EloquentReceiptRepository;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\InvoiceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(ReceiptRepositoryInterface::class, EloquentReceiptRepository::class);
        $this->app->bind(InvoiceServiceInterface::class, InvoiceService::class);

        $this->app->bind(TaxCalculatorInterface::class, function ($app): TaxCalculatorInterface {
            // Jobs bind 'tax.country' before resolving; HTTP requests fall back to auth user.
            $country = $app->bound('tax.country')
                ? $app->make('tax.country')
                : (auth()->user()?->company?->country ?? 'US');

            return match ($country) {
                'TR'    => new TurkeyTaxAdapter(),
                'UK'    => new UKTaxAdapter(),
                default => new USTaxAdapter(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}

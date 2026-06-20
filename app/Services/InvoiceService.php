<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
    ) {}

    public function getCompanyInvoices(int $companyId): Collection
    {
        return $this->invoiceRepository->findByCompany($companyId);
    }

    public function createInvoice(array $data): Invoice
    {
        return $this->invoiceRepository->create($data);
    }
}

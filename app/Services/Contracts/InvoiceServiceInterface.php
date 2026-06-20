<?php

namespace App\Services\Contracts;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceServiceInterface
{
    public function getCompanyInvoices(int $companyId): Collection;

    public function createInvoice(array $data): Invoice;
}

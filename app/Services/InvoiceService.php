<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

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
        $items = array_map(function (array $item): array {
            $item['subtotal'] = $item['quantity'] * $item['unit_price'];

            return $item;
        }, $data['items']);

        $invoiceData = array_merge(
            collect($data)->except('items')->all(),
            ['total_amount' => array_sum(array_column($items, 'subtotal'))],
        );

        $invoice = $this->invoiceRepository->createWithItems($invoiceData, $items);

        Log::channel('audit')->info('Invoice created', [
            'invoice_id'   => $invoice->id,
            'company_id'   => $invoice->company_id,
            'total_amount' => $invoice->total_amount,
            'currency'     => $invoice->currency,
            'item_count'   => count($items),
        ]);

        return $invoice;
    }
}

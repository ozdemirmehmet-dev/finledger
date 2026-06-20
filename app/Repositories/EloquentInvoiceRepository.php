<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice
    {
        return Invoice::with('items')->find($id);
    }

    public function findByCompany(int $companyId): Collection
    {
        return Invoice::where('company_id', $companyId)
            ->with('items')
            ->latest()
            ->get();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function createWithItems(array $invoiceData, array $items): Invoice
    {
        return DB::transaction(function () use ($invoiceData, $items): Invoice {
            $invoice = Invoice::create($invoiceData);
            $invoice->items()->createMany($items);

            return $invoice->load('items');
        });
    }

    public function update(int $id, array $data): Invoice
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update($data);

        return $invoice->fresh('items');
    }

    public function delete(int $id): bool
    {
        return Invoice::findOrFail($id)->delete();
    }
}

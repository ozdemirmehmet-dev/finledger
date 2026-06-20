<?php

namespace App\Repositories;

use App\Models\Receipt;
use App\Repositories\Contracts\ReceiptRepositoryInterface;
use Carbon\Carbon;

class EloquentReceiptRepository implements ReceiptRepositoryInterface
{
    public function findById(int $id): ?Receipt
    {
        return Receipt::find($id);
    }

    public function updateStatus(int $id, string $status): void
    {
        Receipt::findOrFail($id)->update(['status' => $status]);
    }

    public function saveExtracted(int $id, float $amount, Carbon $date): void
    {
        Receipt::findOrFail($id)->update([
            'extracted_amount' => $amount,
            'extracted_date'   => $date,
            'status'           => 'done',
            'processed_at'     => now(),
        ]);
    }
}

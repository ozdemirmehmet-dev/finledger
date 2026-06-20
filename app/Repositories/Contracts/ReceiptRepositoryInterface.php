<?php

namespace App\Repositories\Contracts;

use App\Models\Receipt;
use Carbon\Carbon;

interface ReceiptRepositoryInterface
{
    public function findById(int $id): ?Receipt;

    public function updateStatus(int $id, string $status): void;

    public function saveExtracted(int $id, float $amount, Carbon $date): void;
}

<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice;

    public function findByCompany(int $companyId): Collection;

    public function create(array $data): Invoice;

    public function update(int $id, array $data): Invoice;

    public function delete(int $id): bool;
}

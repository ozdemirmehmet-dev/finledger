<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceiptRequest;
use App\Jobs\ProcessReceiptJob;
use App\Models\Receipt;
use App\Repositories\Contracts\ReceiptRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiptRepositoryInterface $receiptRepository,
    ) {}

    public function store(StoreReceiptRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return response()->json(['message' => 'No company associated with this account.'], 422);
        }

        $path = $request->file('receipt')->store('receipts');

        $receipt = Receipt::create([
            'company_id' => $company->id,
            'file_path'  => $path,
        ]);

        ProcessReceiptJob::dispatch($receipt);

        return response()->json(['id' => $receipt->id, 'status' => $receipt->status], 202);
    }

    public function status(Request $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return response()->json(['message' => 'No company associated with this account.'], 422);
        }

        $receipt = $this->receiptRepository->findById($id);

        if (! $receipt || $receipt->company_id !== $company->id) {
            return response()->json(['message' => 'Receipt not found.'], 404);
        }

        return response()->json([
            'id'               => $receipt->id,
            'status'           => $receipt->status,
            'extracted_amount' => $receipt->extracted_amount,
            'extracted_date'   => $receipt->extracted_date,
            'processed_at'     => $receipt->processed_at,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceServiceInterface $invoiceService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return response()->json(['message' => 'No company associated with this account.'], 422);
        }

        $invoices = $this->invoiceService->getCompanyInvoices($company->id);

        return response()->json($invoices);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return response()->json(['message' => 'No company associated with this account.'], 422);
        }

        $invoice = $this->invoiceService->createInvoice(
            array_merge($request->validated(), ['company_id' => $company->id])
        );

        return response()->json($invoice, 201);
    }
}

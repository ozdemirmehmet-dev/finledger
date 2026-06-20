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
        $companyId = $request->user()->company->id;

        $invoices = $this->invoiceService->getCompanyInvoices($companyId);

        return response()->json($invoices);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $companyId = $request->user()->company->id;

        $invoice = $this->invoiceService->createInvoice(
            array_merge($request->validated(), ['company_id' => $companyId])
        );

        return response()->json($invoice, 201);
    }
}

<?php

namespace App\Jobs;

use App\Adapters\Tax\Contracts\TaxCalculatorInterface;
use App\Models\Receipt;
use App\Repositories\Contracts\ReceiptRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessReceiptJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(private readonly Receipt $receipt) {}

    public function handle(ReceiptRepositoryInterface $receiptRepository): void
    {
        Log::channel('queue')->info('ProcessReceiptJob started', [
            'receipt_id' => $this->receipt->id,
            'company_id' => $this->receipt->company_id,
            'attempt'    => $this->attempts(),
        ]);

        $receiptRepository->updateStatus($this->receipt->id, 'processing');

        // Simulate OCR extraction
        $extractedAmount = round(fake()->randomFloat(2, 100, 5000), 2);
        $extractedDate   = now();

        // Resolve the correct tax adapter for this company's country at job runtime
        $country = $this->receipt->company->country;
        app()->bind('tax.country', fn () => $country);
        /** @var TaxCalculatorInterface $taxCalculator */
        $taxCalculator = app(TaxCalculatorInterface::class);

        $defaultCategory = match ($country) {
            'TR'    => 'general',
            'UK'    => 'standard',
            default => 'standard',
        };

        $taxAmount = $taxCalculator->calculate($extractedAmount, $defaultCategory);

        $receiptRepository->saveExtracted($this->receipt->id, $extractedAmount, $extractedDate);

        Log::channel('queue')->info('ProcessReceiptJob completed', [
            'receipt_id'       => $this->receipt->id,
            'extracted_amount' => $extractedAmount,
            'tax_amount'       => $taxAmount,
            'country'          => $country,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        app(ReceiptRepositoryInterface::class)->updateStatus($this->receipt->id, 'failed');

        Log::channel('queue')->error('ProcessReceiptJob failed', [
            'receipt_id' => $this->receipt->id,
            'error'      => $e->getMessage(),
            'attempt'    => $this->attempts(),
        ]);
    }
}

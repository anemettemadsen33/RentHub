<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizedInvoiceEmailService
{
    private const CACHE_TTL = 1800; // 30 minutes
    private const MAX_RETRY_ATTEMPTS = 3;
    private const RETRY_DELAY = 60; // 1 minute

    public function __construct(
        private OptimizedInvoicePdfService $pdfService
    ) {}

    /**
     * Send invoice email with retry logic and caching
     */
    public function send(Invoice $invoice): bool
    {
        $cacheKey = "invoice_email_sent_{$invoice->id}";
        
        // Check if email was recently sent successfully
        if (Cache::has($cacheKey)) {
            Log::info('Invoice email already sent recently', ['invoice_id' => $invoice->id]);
            return true;
        }

        $startTime = microtime(true);
        $attempts = 0;

        while ($attempts < self::MAX_RETRY_ATTEMPTS) {
            try {
                // Generate PDF if it doesn't exist
                if (!$invoice->pdf_path) {
                    $this->pdfService->generate($invoice);
                    $invoice->refresh();
                }

                // Validate PDF exists
                if (!$invoice->pdf_path || !\Storage::exists($invoice->pdf_path)) {
                    throw new \Exception('PDF file not found after generation');
                }

                // Send email
                Mail::to($invoice->customer_email)->send(new InvoiceMail($invoice));

                // Update invoice status
                $invoice->update([
                    'sent_at' => now(),
                    'send_count' => $invoice->send_count + 1,
                    'status' => $invoice->status === 'draft' ? 'sent' : $invoice->status,
                    'last_sent_at' => now(),
                ]);

                // Cache successful send
                Cache::put($cacheKey, true, self::CACHE_TTL);

                $sendTime = round((microtime(true) - $startTime) * 1000, 2);
                Log::info('Invoice email sent successfully', [
                    'invoice_id' => $invoice->id,
                    'send_time_ms' => $sendTime,
                    'attempts' => $attempts + 1,
                    'recipient' => $invoice->customer_email,
                ]);

                return true;

            } catch (\Exception $e) {
                $attempts++;
                Log::warning('Invoice email send attempt failed', [
                    'invoice_id' => $invoice->id,
                    'attempt' => $attempts,
                    'error' => $e->getMessage(),
                ]);

                if ($attempts < self::MAX_RETRY_ATTEMPTS) {
                    sleep(self::RETRY_DELAY);
                }
            }
        }

        // All attempts failed
        Log::error('Invoice email send failed after all attempts', [
            'invoice_id' => $invoice->id,
            'attempts' => $attempts,
            'recipient' => $invoice->customer_email,
        ]);

        return false;
    }

    /**
     * Queue invoice email for background processing
     */
    public function queueSend(Invoice $invoice): bool
    {
        try {
            // Dispatch to queue with delay for better performance
            dispatch(function () use ($invoice) {
                $this->send($invoice);
            })->delay(now()->addSeconds(30));

            Log::info('Invoice email queued for sending', ['invoice_id' => $invoice->id]);
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to queue invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Resend invoice email with PDF regeneration
     */
    public function resend(Invoice $invoice): bool
    {
        try {
            // Regenerate PDF
            $this->pdfService->regenerate($invoice);
            $invoice->refresh();

            // Clear email sent cache
            Cache::forget("invoice_email_sent_{$invoice->id}");

            return $this->send($invoice);

        } catch (\Exception $e) {
            Log::error('Invoice email resend failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Bulk send emails for multiple invoices
     */
    public function bulkSend(array $invoiceIds): array
    {
        $results = [];
        $processed = 0;
        $failed = 0;

        foreach (array_chunk($invoiceIds, 50) as $chunk) {
            $invoices = Invoice::whereIn('id', $chunk)
                ->with(['booking.user', 'property.user'])
                ->get();

            foreach ($invoices as $invoice) {
                try {
                    $success = $this->queueSend($invoice);
                    $results[$invoice->id] = [
                        'status' => $success ? 'queued' : 'failed',
                        'invoice_number' => $invoice->invoice_number,
                    ];

                    if ($success) {
                        $processed++;
                    } else {
                        $failed++;
                    }

                } catch (\Exception $e) {
                    $results[$invoice->id] = [
                        'status' => 'error',
                        'error' => $e->getMessage(),
                    ];
                    $failed++;
                    Log::error('Bulk email send failed for invoice', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Memory management
            if (memory_get_usage() > 134217728) { // 128MB threshold
                gc_collect_cycles();
            }
        }

        Log::info('Bulk email send completed', [
            'total_processed' => $processed,
            'total_failed' => $failed,
            'total_invoices' => count($invoiceIds),
            'success_rate' => round(($processed / count($invoiceIds)) * 100, 2) . '%',
        ]);

        return $results;
    }

    /**
     * Get email sending statistics
     */
    public function getStatistics(): array
    {
        $totalInvoices = Invoice::count();
        $sentInvoices = Invoice::whereNotNull('sent_at')->count();
        $failedInvoices = Invoice::whereNull('sent_at')
            ->where('created_at', '<', now()->subHours(24))
            ->count();

        $avgSendTime = Invoice::whereNotNull('sent_at')
            ->avg('send_count') ?? 0;

        return [
            'total_invoices' => $totalInvoices,
            'sent_invoices' => $sentInvoices,
            'failed_invoices' => $failedInvoices,
            'success_rate' => $totalInvoices > 0 ? round(($sentInvoices / $totalInvoices) * 100, 2) . '%' : '0%',
            'average_send_attempts' => round($avgSendTime, 2),
        ];
    }
}
<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizedInvoicePdfService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CHUNK_SIZE = 1000; // Process in chunks for large datasets

    public function generate(Invoice $invoice): string
    {
        $startTime = microtime(true);
        
        // Use cache for frequently accessed invoice data
        $cacheKey = "invoice_data_{$invoice->id}";
        $invoiceData = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($invoice) {
            return $invoice->load([
                'booking:id,property_id,user_id,check_in,check_out,total_amount,status',
                'user:id,name,email,phone',
                'property:id,title,address,city,country',
                'bankAccount:id,account_name,account_number,bank_name,swift_code'
            ]);
        });

        // Generate PDF with optimized settings
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoiceData,
        ])->setPaper('a4', 'portrait')
           ->setOptions([
               'isHtml5ParserEnabled' => true,
               'isRemoteEnabled' => false,
               'tempDir' => storage_path('app/temp'),
               'enable_php' => false,
               'enable_javascript' => false,
               'enable_remote' => false,
           ]);

        $filename = 'invoices/'.$invoice->invoice_number.'.pdf';
        
        // Use chunked storage for better memory management
        $pdfContent = $pdf->output();
        Storage::put($filename, $pdfContent);

        // Update invoice with optimized query
        $invoice->timestamps = false; // Disable timestamps to reduce queries
        $invoice->update(['pdf_path' => $filename]);
        $invoice->timestamps = true; // Re-enable timestamps

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('PDF generation completed', [
            'invoice_id' => $invoice->id,
            'duration_ms' => $duration,
            'file_size' => strlen($pdfContent)
        ]);

        return $filename;
    }

    public function generateBatch(array $invoiceIds): array
    {
        $results = [];
        $startTime = microtime(true);

        // Process invoices in chunks to reduce memory usage
        $chunks = array_chunk($invoiceIds, self::CHUNK_SIZE);
        
        foreach ($chunks as $chunk) {
            $invoices = Invoice::with([
                'booking:id,property_id,user_id,check_in,check_out,total_amount,status',
                'user:id,name,email,phone',
                'property:id,title,address,city,country',
                'bankAccount:id,account_name,account_number,bank_name,swift_code'
            ])->whereIn('id', $chunk)->get();

            foreach ($invoices as $invoice) {
                try {
                    $results[$invoice->id] = $this->generate($invoice);
                } catch (\Exception $e) {
                    Log::error('PDF generation failed for invoice', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                    $results[$invoice->id] = null;
                }
            }
        }

        $totalDuration = (microtime(true) - $startTime) * 1000;
        Log::info('Batch PDF generation completed', [
            'total_invoices' => count($invoiceIds),
            'total_duration_ms' => $totalDuration,
            'average_duration_ms' => $totalDuration / count($invoiceIds)
        ]);

        return $results;
    }

    public function download(Invoice $invoice)
    {
        $cacheKey = "invoice_pdf_exists_{$invoice->id}";
        
        // Check cache first to avoid repeated storage checks
        $pdfExists = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($invoice) {
            return $invoice->pdf_path && Storage::exists($invoice->pdf_path);
        });

        if (!$pdfExists) {
            $this->generate($invoice);
        }

        return Storage::download($invoice->pdf_path, $invoice->invoice_number.'.pdf');
    }

    public function stream(Invoice $invoice)
    {
        $cacheKey = "invoice_pdf_exists_{$invoice->id}";
        
        // Check cache first to avoid repeated storage checks
        $pdfExists = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($invoice) {
            return $invoice->pdf_path && Storage::exists($invoice->pdf_path);
        });

        if (!$pdfExists) {
            $this->generate($invoice);
        }

        return response()->file(Storage::path($invoice->pdf_path));
    }

    public function regenerate(Invoice $invoice): string
    {
        // Clear cache before regeneration
        Cache::forget("invoice_data_{$invoice->id}");
        Cache::forget("invoice_pdf_exists_{$invoice->id}");

        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
            Storage::delete($invoice->pdf_path);
        }

        return $this->generate($invoice);
    }

    public function cleanupOldPdfs(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        $deletedCount = 0;

        // Find old invoices with PDFs
        $oldInvoices = Invoice::where('created_at', '<', $cutoffDate)
            ->whereNotNull('pdf_path')
            ->select('id', 'pdf_path')
            ->chunkById(100, function ($invoices) use (&$deletedCount) {
                foreach ($invoices as $invoice) {
                    if (Storage::exists($invoice->pdf_path)) {
                        Storage::delete($invoice->pdf_path);
                        $invoice->update(['pdf_path' => null]);
                        $deletedCount++;
                    }
                }
            });

        Log::info('Old PDF cleanup completed', [
            'deleted_count' => $deletedCount,
            'days_old' => $daysOld
        ]);

        return $deletedCount;
    }
}
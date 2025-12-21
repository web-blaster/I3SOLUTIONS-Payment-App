<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\PaymentRowLog;
use App\Models\PaymentUpload;
use App\Services\ExchangeRateService;
use App\Support\Payments\PaymentCsvRowParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPaymentCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // tune these
    private int $chunkSize = 1000;     // insert per 1000 rows
    private int $progressEvery = 5000; // update progress per 5k rows

    public function __construct(public int $uploadId) {}

    public function handle(ExchangeRateService $fx, PaymentCsvRowParser $parser): void
    {
        $upload = PaymentUpload::findOrFail($this->uploadId);

        $upload->update([
            'status' => 'PROCESSING',
            'started_at' => now(),
            'error_message' => null,
        ]);

        $disk = $upload->s3_disk;
        $key  = $upload->s3_key;

        $stream = Storage::disk($disk)->readStream($key);
        if ($stream === false) {
            $upload->update([
                'status' => 'FAILED',
                'completed_at' => now(),
                'error_message' => 'Unable to open CSV stream from storage.',
            ]);
            return;
        }

        $success = 0;
        $failed  = 0;
        $processed = 0;

        $paymentsBatch = [];
        $logsBatch = [];

        // in-job rate cache
        $rateCache = [];

        try {
            // --- Read header ---
            $headerRow = fgetcsv($stream);
            if (!$headerRow || count($headerRow) < 2) {
                $upload->update([
                    'status' => 'FAILED',
                    'completed_at' => now(),
                    'error_message' => 'CSV is empty or missing header.',
                ]);
                return;
            }

            $header = array_map(fn($h) => trim((string)$h), $headerRow);

            $required = ['customer_id', 'customer_name', 'customer_email', 'amount', 'currency', 'reference_no', 'date_time'];
            $missing = array_values(array_diff($required, $header));
            if (!empty($missing)) {
                $upload->update([
                    'status' => 'FAILED',
                    'completed_at' => now(),
                    'error_message' => 'Missing columns: ' . implode(', ', $missing),
                ]);
                return;
            }

            $idx = array_flip($header);

            // --- Stream rows ---
            $rowNumber = 1; // header is row 1
            while (($raw = fgetcsv($stream)) !== false) {
                $rowNumber++; // data rows start at 2
                $processed++;

                // build row associative
                $row = [];
                foreach ($required as $col) {
                    $row[$col] = $raw[$idx[$col]] ?? null;
                }

                try {
                    $clean = $parser->validateAndNormalize($row);

                    // rate cached (in-memory)
                    $cur = $clean['currency'];
                    if (!isset($rateCache[$cur])) {
                        $rateCache[$cur] = $fx->rateToUsd($cur); // service already uses Cache::remember
                    }
                    $rate = (string)$rateCache[$cur];

                    $usdAmount = bcmul((string)$clean['amount'], $rate, 6);

                    $now = now();

                    $paymentsBatch[] = [
                        'upload_id' => $upload->id,
                        'customer_id' => $clean['customer_id'],
                        'customer_name' => $clean['customer_name'],
                        'customer_email' => $clean['customer_email'],
                        'amount' => $clean['amount'],
                        'currency' => $clean['currency'],
                        'usd_amount' => $usdAmount,
                        'usd_rate' => $rate,
                        'reference_no' => $clean['reference_no'],
                        'payment_at' => $clean['payment_at'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $logsBatch[] = [
                        'upload_id' => $upload->id,
                        'row_number' => $rowNumber,
                        'status' => 'SUCCESS',
                        'reference_no' => $clean['reference_no'],
                        'message' => 'Stored successfully',
                        'raw' => json_encode($row, JSON_UNESCAPED_UNICODE),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $success++;
                } catch (Throwable $e) {
                    $failed++;

                    $now = now();

                    $logsBatch[] = [
                        'upload_id' => $upload->id,
                        'row_number' => $rowNumber,
                        'status' => 'FAILED',
                        'reference_no' => $row['reference_no'] ?? null,
                        'message' => $e->getMessage(),
                        'raw' => json_encode($row, JSON_UNESCAPED_UNICODE),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // keep warning lightweight (don’t log 1M lines)
                    if ($failed <= 20) {
                        Log::warning('CSV row failed', [
                            'upload_id' => $upload->id,
                            'row_number' => $rowNumber,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // --- Flush batches ---
                if (count($paymentsBatch) >= $this->chunkSize) {
                    $this->flushBatches($paymentsBatch, $logsBatch);
                    $paymentsBatch = [];
                    $logsBatch = [];
                }

                // --- Progress update (optional) ---
                if ($processed % $this->progressEvery === 0) {
                    $upload->update([
                        'success_rows' => $success,
                        'failed_rows' => $failed,
                    ]);
                }
            }

            // flush remaining
            $this->flushBatches($paymentsBatch, $logsBatch);

            $upload->update([
                'status' => 'COMPLETED',
                'success_rows' => $success,
                'failed_rows' => $failed,
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $upload->update([
                'status' => 'FAILED',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }


    //bulk insert
    private function flushBatches(array $paymentsBatch, array $logsBatch): void
    {
        // Bulk insert
        if (!empty($paymentsBatch)) {
            Payment::insert($paymentsBatch);
        }
        if (!empty($logsBatch)) {
            PaymentRowLog::insert($logsBatch);
        }
    }
    //bulk insert
}

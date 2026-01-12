<?php

namespace App\Services;

use App\Jobs\ProcessPaymentCsvJob;
use App\Models\PaymentUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PaymentUploadService
{

    //upload and make queue
    public function uploadAndQueue(int $userId, UploadedFile $file): PaymentUpload
    {
        $disk = config('filesystems.default');

        $origName = $file->getClientOriginalName();
        $safeName = Str::slug(pathinfo($origName, PATHINFO_FILENAME));
        $ext      = $file->getClientOriginalExtension();
        $finalName = $safeName . ($ext ? '.' . $ext : '');

        $key = "payment_uploads/{$userId}/" . now()->format('Ymd_His') . "_{$finalName}";

        // 1) Upload first (can't roll back anyway)
        try {
            Storage::disk($disk)->put($key, file_get_contents($file));
        } catch (Throwable $e) {
            Log::error('CSV S3 upload failed', [
                'user_id' => $userId,
                'disk'    => $disk,
                'key'     => $key,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }

        // 2) DB + queue in a transaction
        try {
            return DB::transaction(function () use ($userId, $origName, $disk, $key, $file) {

                $upload = PaymentUpload::create([
                    'user_id'           => $userId,
                    'original_filename' => $origName,
                    's3_disk'           => $disk,
                    's3_key'            => $key,
                    'status'            => 'QUEUED',
                ]);

                // IMPORTANT: dispatch only AFTER commit
                ProcessPaymentCsvJob::dispatch($upload->id)->afterCommit();

                Log::info('CSV uploaded and queued', [
                    'upload_id'  => $upload->id,
                    'user_id'    => $userId,
                    'disk'       => $disk,
                    'key'        => $key,
                    'size_bytes' => $file->getSize(),
                ]);

                return $upload;
            });
        } catch (Throwable $e) {
            // DB failed AFTER S3 succeeded => cleanup the S3 file
            try {
                Storage::disk($disk)->delete($key);
            } catch (Throwable $cleanup) {
                Log::warning('CSV cleanup failed after DB error', [
                    'user_id' => $userId,
                    'disk'    => $disk,
                    'key'     => $key,
                    'error'   => $cleanup->getMessage(),
                ]);
            }

            Log::error('CSV uploadAndQueue failed (DB/dispatch)', [
                'user_id' => $userId,
                'disk'    => $disk,
                'key'     => $key,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }
    //upload and make queue
}

<?php

namespace App\Services;

use App\Jobs\ProcessPaymentCsvJob;
use App\Models\PaymentUpload;
use Illuminate\Http\UploadedFile;
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

        // safer filename (avoids spaces/odd chars)
        $origName = $file->getClientOriginalName();
        $safeName = Str::slug(pathinfo($origName, PATHINFO_FILENAME));
        $ext = $file->getClientOriginalExtension();
        $finalName = $safeName . ($ext ? '.' . $ext : '');

        $key = "payment_uploads/{$userId}/" . now()->format('Ymd_His') . "_{$finalName}";


        try {

            Storage::disk($disk)->put($key, file_get_contents($file));


            //insert into payment_uploads table
            $upload = PaymentUpload::create([
                'user_id' => $userId,
                'original_filename' => $origName,
                's3_disk' => $disk,
                's3_key' => $key,
                'status' => 'QUEUED',
            ]);
            //insert into payment_uploads table

            ProcessPaymentCsvJob::dispatch($upload->id);

            // ✅ success log (goes to daily if LOG_CHANNEL=daily)
            Log::info('CSV uploaded and queued', [
                'upload_id' => $upload->id,
                'user_id' => $userId,
                'disk' => $disk,
                'key' => $key,
                'size_bytes' => $file->getSize(),
            ]);

            return $upload;
        } catch (Throwable $e) {
            Log::error('CSV uploadAndQueue failed', [
                'user_id' => $userId,
                'disk' => $disk,
                'key' => $key ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
    //upload and make queue
}

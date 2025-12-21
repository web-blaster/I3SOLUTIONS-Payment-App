<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentUpload extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'user_id',
        'original_filename',
        's3_disk',
        's3_key',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'started_at',
        'completed_at',
        'error_message'
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'upload_id');
    }
    public function rowLogs()
    {
        return $this->hasMany(PaymentRowLog::class, 'upload_id');
    }

    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRowLog extends Model
{


    protected $casts = [
        'raw' => 'array',
    ];

    public function upload()
    {
        return $this->belongsTo(PaymentUpload::class, 'upload_id');
    }
}

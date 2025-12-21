<?php

namespace App\Http\Controllers;

use App\Models\PaymentRowLog;
use App\Services\PaymentUploadService;
use App\Models\PaymentUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentFailController extends Controller
{


    //list of payments fails
    public function index(Request $request)
    {
        $failList = PaymentRowLog::where('status', 'FAILED')
            ->whereHas('upload', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->with('upload:id,original_filename')
            ->latest()
            ->paginate(10);

        return view('payments.fail-list', compact('failList'));
    }
    //list of payments fails
}

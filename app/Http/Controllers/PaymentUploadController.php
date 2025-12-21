<?php

namespace App\Http\Controllers;

use App\Services\PaymentUploadService;
use App\Models\PaymentUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentUploadController extends Controller
{
    public function __construct(private PaymentUploadService $service) {}

    public function index(Request $request)
    {
        $uploads = PaymentUpload::where('user_id', $request->user()->id)
            ->latest()
            ->withCount('payments')
            ->paginate(10);

        return view('payments.index', compact('uploads'));
    }




    //upload into web
    public function uploadWeb(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:204800'], // ✅ 200MB
        ]);


        try {

            $this->service->uploadAndQueue($request->user()->id, $request->file('file'));

            return redirect()
                ->route('payments.index')
                ->with('success', 'File uploaded. Processing started.');
        } catch (Throwable $e) {
            Log::error('CSV upload failed (web)', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors('Upload failed. Please try again later.');
        }
    }
    //upload via web

    //upload via api
    public function uploadApi(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:204800'], // 
        ]);

        try {


            $upload = $this->service->uploadAndQueue(Auth::user()->id, $request->file('file'));

            return response()->json([
                'status' => true,
                'message' => 'File uploaded. Processing started.',
                'upload_id' => $upload->id,
            ]);
        } catch (Throwable $e) {
            Log::error('CSV upload failed (api)', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Upload failed',
            ], 500);
        }
    }
    //upload via api
}

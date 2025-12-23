<?php

use App\Http\Controllers\PaymentFailController;
use App\Http\Controllers\PaymentUploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/healthz', fn () => response('ok', 200));


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::post('/payments/upload', [PaymentUploadController::class, 'uploadWeb'])
    ->middleware(['auth', 'throttle:payment-upload-web'])->name('payments.upload.web');

Route::middleware('auth')->group(function () {


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/payments', [PaymentUploadController::class, 'index'])
        ->name('payments.index');

    Route::get('/payments.fails', [PaymentFailController::class, 'index'])
        ->name('payments.fails');

});

require __DIR__ . '/auth.php';

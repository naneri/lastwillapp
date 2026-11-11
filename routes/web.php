<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\CheckInConfirmationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OwnerCheckInController;
use App\Http\Controllers\PaddleWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\RevealController;
use App\Http\Controllers\WillReceiverController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/record', [RecordController::class, 'edit'])->name('record.edit');
    Route::patch('/record', [RecordController::class, 'update'])->name('record.update');

    Route::get('/will-receivers', [WillReceiverController::class, 'edit'])->name('will-receivers.edit');
    Route::put('/will-receivers', [WillReceiverController::class, 'update'])->name('will-receivers.update');

    Route::post('/checkin', [OwnerCheckInController::class, 'store'])->name('owner-checkin.store');

    Route::get('/billing', [BillingController::class, 'show'])->name('billing.show');
    Route::get('/billing/status', [BillingController::class, 'status'])->name('billing.status');
    Route::post('/billing/upgrade/{plan}', [BillingController::class, 'upgrade'])->name('billing.upgrade');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});

Route::get('/checkin/{token}', [CheckInConfirmationController::class, 'show'])->name('checkin.confirm');

Route::get('/reveal/{token}', [RevealController::class, 'show'])->name('reveal.show');
Route::post('/reveal/{token}/confirm', [RevealController::class, 'confirm'])->name('reveal.confirm');

Route::post('/webhooks/paddle', [PaddleWebhookController::class, 'handle'])->name('webhooks.paddle');

require __DIR__.'/auth.php';

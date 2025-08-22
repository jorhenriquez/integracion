<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookEventController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::post('/webhook/dispatchtrack', [WebhookController::class, 'dispatchtrack']);
Route::post('/webhook', [WebhookEventController::class, 'receive']);
Route::get('/webhook-events', [WebhookEventController::class, 'index']);
Route::get('/webhook-events/{id}', [WebhookEventController::class, 'show']);
Route::patch('/webhook-events/{id}/process', [WebhookEventController::class, 'markAsProcessed']);

require __DIR__.'/auth.php';

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DisplayController;

// General Portal Entry
Route::get('/', function () {
    return view('welcome');
});

// Admin Dashboard Routes
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/call-next', [AdminController::class, 'callNext'])->name('admin.call-next');
    Route::post('/serve/{token}', [AdminController::class, 'serve'])->name('admin.serve');
    Route::post('/skip/{token}', [AdminController::class, 'skip'])->name('admin.skip');
    Route::post('/recall/{token}', [AdminController::class, 'recall'])->name('admin.recall');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.update-settings');
    Route::post('/reset', [AdminController::class, 'resetQueue'])->name('admin.reset');
    Route::get('/print', [AdminController::class, 'printTemplate'])->name('admin.print');
});

// Client Scan Routes
Route::get('/scan', [ClientController::class, 'index'])->name('client.scan');
Route::post('/scan/submit', [ClientController::class, 'scan'])->name('client.scan-submit');

// TV Display Routes
Route::get('/tv', [DisplayController::class, 'index'])->name('display.tv');
Route::get('/tv/status', [DisplayController::class, 'status'])->name('display.tv-status');

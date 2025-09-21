<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;

Route::middleware('auth')->group(function () {
    Route::get('/pelanggan', [CustomerController::class, 'list'])->name('customers.list');
    Route::get('/pelanggan/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::post('/pelanggan/{customer}/edit', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/pelanggan/{customer}/riwayat', [CustomerController::class, 'riwayat'])->name('customers.riwayat');
    Route::delete('/pelanggan/{customer}/delete', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

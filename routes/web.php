
<?php
use App\Http\Controllers\CustomerController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillingController;

// Route publik untuk akses invoice tanpa login
Route::get('/invoice/{invoice_link}', [BillingController::class, 'showInvoice'])->name('invoice.show');
Route::post('/invoice/{invoice}/konfirmasi', [\App\Http\Controllers\BillingController::class, 'confirmPayment'])->name('invoice.confirm-payment');

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Aktivasi/tambah pelanggan
    Route::get('/pelanggan/aktivasi', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/pelanggan/aktivasi', [CustomerController::class, 'store'])->name('customers.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/ikan', function () {return view('welcome');});

    // Menu penagihan
    Route::get('/penagihan', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/penagihan/{customer}/buat-tagihan', [BillingController::class, 'createInvoice'])->name('billing.create-invoice');
    Route::post('/penagihan/invoice/{invoice}/tolak', [BillingController::class, 'tolakPembayaran'])->name('billing.tolak-pembayaran');
    Route::post('/penagihan/invoice/{invoice}/konfirmasi', [BillingController::class, 'confirmPayment'])->name('billing.confirm-payment');
});

require __DIR__.'/auth.php';
require __DIR__.'/customers.php';
require __DIR__.'/odp.php';
require __DIR__.'/pengeluaran.php';

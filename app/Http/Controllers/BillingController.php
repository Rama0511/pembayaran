<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BillingController extends Controller
{
    public function confirmPayment($invoiceId)
    {
        $invoice = \App\Models\Invoice::findOrFail($invoiceId);
        $paidAmount = request()->input('paid_amount');
        if ($paidAmount && $paidAmount > 0) {
            $invoice->amount = $paidAmount;
        }

        // Handle upload bukti pembayaran (opsional)
        if (request()->hasFile('bukti_pembayaran')) {
            $file = request()->file('bukti_pembayaran');
            $path = $file->store('bukti_pembayaran', 'public');
            $invoice->bukti_pembayaran = $path;
            $invoice->tolak_info = null; // reset info tolak jika ada upload baru
        }


        // Jika admin (dari dashboard) konfirmasi, bisa kapan saja
        if (Auth::check() && Auth::user()->role === 'admin') {
            $invoice->status = 'paid';
            $invoice->paid_at = now();
            $invoice->tolak_info = null; // reset info tolak jika sudah dikonfirmasi

            // Update due_date customer: due_date lama + 30 hari
            $customer = $invoice->customer;
            if ($customer && $invoice->due_date) {
                $oldDue = \Carbon\Carbon::parse($invoice->due_date);
                $customer->due_date = $oldDue->copy()->addDays(30);
                $customer->save();
            }
        } else {
            // Jika dari publik (bukan admin), status selalu jadi menunggu konfirmasi (kecuali sudah paid)
            if ($invoice->status !== 'paid') {
                $invoice->status = 'menunggu konfirmasi';
                $invoice->paid_at = null;
            }
        }

        $invoice->save();

        // Redirect sesuai asal request
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('billing.index')->with('success', 'Pembayaran dikonfirmasi.');
        }
        $invoice_link = $invoice->invoice_link;
        return redirect()->route('invoice.show', $invoice_link)->with('success', 'Konfirmasi pembayaran berhasil dikirim.');
    }
    // Tampilkan invoice berdasarkan link unik, tanpa login
    public function showInvoice($invoice_link)
    {
    $invoice = \App\Models\Invoice::where('invoice_link', $invoice_link)->with('customer')->firstOrFail();
    $hideNavbar = !\Illuminate\Support\Facades\Auth::check();
    return view('billing.invoice', compact('invoice', 'hideNavbar'));
    }

    public function index()
    {
        $today = Carbon::today();
        $customers = Customer::all();

        $late = $customers->filter(function($c) use ($today) {
            return $c->due_date && Carbon::parse($c->due_date)->lt($today);
        });

        $almostLate = $customers->filter(function($c) use ($today) {
            return $c->due_date && Carbon::parse($c->due_date)->gte($today) && Carbon::parse($c->due_date)->lte($today->copy()->addDays(7));
        });

        $others = $customers->filter(function($c) use ($late, $almostLate) {
            return !$late->contains($c) && !$almostLate->contains($c);
        });

        // Ambil invoice bulan ini untuk setiap customer (map by id)
        $currentMonth = $today->format('Y-m');
        $invoicesThisMonth = [];
        foreach ($customers as $customer) {
            $invoice = $customer->invoices()
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$currentMonth])
                ->latest('invoice_date')->first();
            $invoicesThisMonth[$customer->id] = $invoice;
        }

        return view('billing.index', [
            'late' => $late,
            'almostLate' => $almostLate,
            'others' => $others,
            'invoicesThisMonth' => $invoicesThisMonth,
        ]);
    }

    public function createInvoice($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $amount = request()->input('amount');
        if (!$amount || $amount <= 0) {
            return redirect()->back()->with('error', 'Nominal tagihan harus diisi.');
        }
        $invoice = $customer->invoices()->create([
            'invoice_date' => now(),
            'due_date' => $customer->due_date ?? now()->addDays(7),
            'amount' => $amount,
            'status' => 'unpaid',
            'invoice_link' => uniqid('inv_'),
        ]);

        // TODO: Kirim link invoice ke pelanggan jika perlu

        return redirect()->route('billing.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    public function tolakPembayaran($invoiceId)
    {
        $invoice = \App\Models\Invoice::findOrFail($invoiceId);
        // Hapus file bukti jika ada
        if ($invoice->bukti_pembayaran) {
            Storage::disk('public')->delete($invoice->bukti_pembayaran);
            $invoice->bukti_pembayaran = null;
        }
        $invoice->status = 'unpaid';
        $invoice->paid_at = null;
        $invoice->tolak_info = 'Bukti pembayaran Anda ditolak. Silakan upload ulang bukti pembayaran yang valid.';
        $invoice->save();
        // Jika admin, redirect ke halaman billing.index, jika publik redirect ke invoice
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('billing.index')->with('error', 'Bukti pembayaran ditolak.');
        }
        return redirect()->route('invoice.show', $invoice->invoice_link)->with('error', 'Bukti pembayaran ditolak. Silakan upload ulang bukti pembayaran yang valid.');
    }
}

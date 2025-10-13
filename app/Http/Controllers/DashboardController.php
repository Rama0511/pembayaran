<?php

namespace App\Http\Controllers;


use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Ringkasan pendapatan bulan ini
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();


        $thisMonthIncome = \App\Models\Invoice::whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'paid')
            ->sum('amount');

        // Ringkasan pendapatan bulan kemarin
        $lastMonth = $now->copy()->subMonth();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        $endOfLastMonth = $lastMonth->copy()->endOfMonth();

        $lastMonthIncome = Invoice::whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('status', 'paid')
            ->sum('amount');

        // Data grafik: pendapatan 12 bulan terakhir
        $monthlyIncome = [];
        $monthLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $income = Invoice::whereBetween('paid_at', [$start, $end])
                ->where('status', 'paid')
                ->sum('amount');
            $monthlyIncome[] = $income;
            $monthLabels[] = $month->format('M Y');
        }

        // Summary pemasangan bulanan (jumlah pelanggan baru per bulan)
        $monthlyInstalls = [];
        $installLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $count = \App\Models\Customer::whereBetween('activation_date', [$start, $end])->count();
            $monthlyInstalls[] = $count;
            $installLabels[] = $month->format('M Y');
        }

        // Pelanggan telat bayar: due_date < hari ini dan belum ada invoice status paid bulan ini
        $today = $now->toDateString();
        $lateCustomers = \App\Models\Customer::where('due_date', '<', $today)
            ->whereDoesntHave('invoices', function($q) use ($startOfMonth, $endOfMonth) {
                $q->where('status', 'paid')->whereBetween('paid_at', [$startOfMonth, $endOfMonth]);
            })->count();

        // Pelanggan sudah bayar bulan ini: ada invoice status paid bulan ini
        $paidCustomers = \App\Models\Customer::whereHas('invoices', function($q) use ($startOfMonth, $endOfMonth) {
            $q->where('status', 'paid')->whereBetween('paid_at', [$startOfMonth, $endOfMonth]);
        })->count();

        return view('dashboard', [
            'thisMonthIncome' => $thisMonthIncome,
            'lastMonthIncome' => $lastMonthIncome,
            'monthlyIncome' => $monthlyIncome,
            'monthLabels' => $monthLabels,
            'monthlyInstalls' => $monthlyInstalls,
            'installLabels' => $installLabels,
            'lateCustomers' => $lateCustomers,
            'paidCustomers' => $paidCustomers,
        ]);
    }
}

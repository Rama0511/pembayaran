<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengeluaranController extends Controller
{
    public function index()
    {
        $pengeluarans = Pengeluaran::with('user')->orderByDesc('tanggal')->get();
        return view('pengeluaran.index', compact('pengeluarans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jumlah' => ['required','regex:/^[0-9,]+$/'],
            'kategori' => 'required|string',
            'detail' => 'nullable|string',
        ]);
        // Hilangkan koma dari jumlah
        $validated['jumlah'] = (int)str_replace(',', '', $validated['jumlah']);
        $validated['user_id'] = Auth::id();
        Pengeluaran::create($validated);
        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }
}

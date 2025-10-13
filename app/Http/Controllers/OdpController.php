<?php

namespace App\Http\Controllers;

use App\Models\Odp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OdpController extends Controller
{
    public function index()
    {
        $odps = Odp::withCount(['customers'])->get();
        return view('odp.index', compact('odps'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|unique:odps,nama',
            'rasio_spesial' => 'nullable|string',
            'rasio_distribusi' => 'required|in:1:2,1:4,1:8,1:16',
            'foto' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('uploads/odp', 'public');
        }
        Odp::create($validated);
        return redirect()->route('odp.index')->with('success', 'ODP berhasil ditambahkan.');
    }

    public function edit(Odp $odp)
    {
        return view('odp.edit', compact('odp'));
    }

    public function update(Request $request, Odp $odp)
    {
        $validated = $request->validate([
            'nama' => 'required|string|unique:odps,nama,' . $odp->id,
            'rasio_spesial' => 'nullable|string',
            'rasio_distribusi' => 'required|in:1:2,1:4,1:8,1:16',
            'foto' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('foto')) {
            if ($odp->foto) Storage::disk('public')->delete($odp->foto);
            $validated['foto'] = $request->file('foto')->store('uploads/odp', 'public');
        }
        $odp->update($validated);
        return redirect()->route('odp.index')->with('success', 'ODP berhasil diupdate.');
    }

    public function show(Odp $odp)
    {
        $odp->load('customers');
        return view('odp.show', compact('odp'));
    }
}

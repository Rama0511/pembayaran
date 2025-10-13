<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function list()
    {
        $query = Customer::query();
        $search = request('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('pppoe_username', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }
        $sort = request('sort');
        if ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'due_asc') {
            $query->orderBy('due_date', 'asc');
        } elseif ($sort === 'due_desc') {
            $query->orderBy('due_date', 'desc');
        } elseif ($sort === 'pppoe_asc') {
            $query->orderBy('pppoe_username', 'asc');
        } elseif ($sort === 'pppoe_desc') {
            $query->orderBy('pppoe_username', 'desc');
        } elseif ($sort === 'wa_asc') {
            $query->orderBy('phone', 'asc');
        } elseif ($sort === 'wa_desc') {
            $query->orderBy('phone', 'desc');
        }
        $customers = $query->get();
        return view('customers.index', compact('customers'));
    }

    public function edit(Customer $customer)
    {
        $odps = \App\Models\Odp::orderBy('nama')->get();
        return view('customers.edit', compact('customer', 'odps'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'activation_date' => 'required|date',
            'due_date' => 'required|date',
            'nik' => 'required|string|max:32',
            'gender' => 'required|in:Pria,Wanita',
            'address' => 'required|string',
            'package_type' => 'required|string',
            'custom_package' => 'required_if:package_type,Paket Custom|string|nullable',
            'pppoe_username' => 'required|string|max:64',
            'odp' => 'required|string|max:64',
            'phone' => 'required|string|max:20',
            'installation_fee' => 'required|numeric',
            'email' => 'nullable|email',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Handle file upload (optional on edit)
        $fileFields = [
            'photo_front', 'photo_modem', 'photo_opm', 'photo_ktp'
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('uploads/customers', 'public');
                // Hapus file lama jika ada
                if ($customer->$field) {
                    Storage::disk('public')->delete($customer->$field);
                }
            }
        }

        $customer->update($validated);
        return redirect()->route('customers.list')->with('success', 'Data pelanggan berhasil diupdate.');
    }

    public function riwayat(Customer $customer)
    {
        $invoices = $customer->invoices()->orderByDesc('invoice_date')->get();
        return view('customers.riwayat', compact('customer', 'invoices'));
    }

    public function create()
    {
        $odps = \App\Models\Odp::orderBy('nama')->get();
        return view('customers.create', compact('odps'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'activation_date' => 'required|date',
            'due_date' => 'required|date',
            'nik' => 'required|string|max:32',
            'gender' => 'required|in:Pria,Wanita',
            'address' => 'required|string',
            'package_type' => 'required|string',
            'custom_package' => 'required_if:package_type,Paket Custom|string|nullable',
            'photo_front' => 'required|image|max:5120',
            'photo_modem' => 'required|image|max:5120',
            'pppoe_username' => 'required|string|max:64',
            'odp' => 'required|string|max:64',
            'phone' => 'required|string|max:20',
            'installation_fee' => 'required|numeric',
            'photo_opm' => 'required|image|max:5120',
            'photo_ktp' => 'required|image|max:5120',
            'email' => 'nullable|email',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Handle file upload (wajib)
        $fileFields = [
            'photo_front', 'photo_modem', 'photo_opm', 'photo_ktp'
        ];
        foreach ($fileFields as $field) {
            $validated[$field] = $request->file($field)->store('uploads/customers', 'public');
        }

        $validated['is_active'] = true;
        Customer::create($validated);
        return redirect()->route('billing.index')->with('success', 'Pelanggan berhasil diaktivasi.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.list')->with('success', 'Pelanggan berhasil dihapus.');
    }
}

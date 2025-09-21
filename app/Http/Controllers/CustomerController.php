<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function list()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
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
        return view('customers.create');
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

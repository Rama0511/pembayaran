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
                  ->orWhere('phone', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
        
        // Check if this is an API request
        if (request('api') || request()->wantsJson()) {
            return response()->json(['data' => $query->get()]);
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

    public function show(Customer $customer)
    {
        return response()->json(['data' => $customer]);
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
            'activation_date' => 'nullable|date',
            'due_date' => 'nullable|string|max:10',
            'nik' => 'nullable|string|max:32',
            'gender' => 'nullable|in:male,female,Pria,Wanita',
            'address' => 'nullable|string',
            'package_type' => 'nullable|string',
            'custom_package' => 'nullable|string',
            'pppoe_username' => 'nullable|string|max:64',
            'odp' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:20',
            'installation_fee' => 'nullable|numeric',
            'email' => 'nullable|email',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'nullable',
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

        // Auto-calculate due_date = activation_date + 30 days (only if activation_date changed and due_date not provided)
        if (!empty($validated['activation_date']) && empty($validated['due_date'])) {
            $activationDate = \Carbon\Carbon::parse($validated['activation_date']);
            $validated['due_date'] = $activationDate->addDays(30)->format('Y-m-d');
        }

        $customer->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json(['data' => $customer, 'message' => 'Customer updated successfully']);
        }
        return redirect()->route('customers.list')->with('success', 'Data pelanggan berhasil diupdate.');
    }

    public function riwayat(Customer $customer)
    {
        $invoices = $customer->invoices()->orderByDesc('invoice_date')->get();
        return view('customers.riwayat', compact('customer', 'invoices'));
    }

    public function riwayatApi(Customer $customer)
    {
        $invoices = $customer->invoices()->orderByDesc('invoice_date')->get();
        return response()->json([
            'customer' => $customer,
            'invoices' => $invoices
        ]);
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
            'activation_date' => 'nullable|date',
            'due_date' => 'nullable|string|max:10',
            'nik' => 'nullable|string|max:32',
            'gender' => 'nullable|in:male,female,Pria,Wanita',
            'address' => 'nullable|string',
            'package_type' => 'nullable|string',
            'custom_package' => 'nullable|string',
            'photo_front' => 'nullable|image|max:5120',
            'photo_modem' => 'nullable|image|max:5120',
            'pppoe_username' => 'nullable|string|max:64',
            'odp' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:20',
            'installation_fee' => 'nullable|numeric',
            'photo_opm' => 'nullable|image|max:5120',
            'photo_ktp' => 'nullable|image|max:5120',
            'email' => 'nullable|email',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'nullable',
        ]);

        // Handle file upload (optional for API)
        $fileFields = [
            'photo_front', 'photo_modem', 'photo_opm', 'photo_ktp'
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('uploads/customers', 'public');
            }
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        
        // Auto-calculate due_date = activation_date + 30 days
        if (!empty($validated['activation_date']) && empty($validated['due_date'])) {
            $activationDate = \Carbon\Carbon::parse($validated['activation_date']);
            $validated['due_date'] = $activationDate->addDays(30)->format('Y-m-d');
        }
        
        $customer = Customer::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json(['data' => $customer, 'message' => 'Customer created successfully'], 201);
        }
        return redirect()->route('billing.index')->with('success', 'Pelanggan berhasil diaktivasi.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Customer deleted successfully']);
        }
        return redirect()->route('customers.list')->with('success', 'Pelanggan berhasil dihapus.');
    }
}

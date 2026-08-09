<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomerPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->withCount(['customerPackages', 'orders'])
            ->withSum('orders', 'profit')
            ->latest()
            ->get();

        return view('admin.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'customer';

        User::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Müşteri başarıyla tanımlandı.');
    }

    public function destroy(User $customer)
    {
        if ($customer->role === 'admin') {
            return back()->with('error', 'Yönetici hesabı silinemez!');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Müşteri ve ilişkili tanımları silindi.');
    }
}

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
            ->withCount(['customerPackages', 'orders', 'branches'])
            ->withSum('orders', 'profit')
            ->with('branches')
            ->latest()
            ->get();

        return view('admin.customers.index', compact('customers'));
    }

    public function storeBranch(Request $request, User $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $customer->branches()->create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', "{$customer->name} müşterisine '{$validated['name']}' şubesi başarıyla eklendi.");
    }

    public function destroyBranch(\App\Models\Branch $branch)
    {
        $branch->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Şube silindi.');
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

    public function addBalance(Request $request, User $customer)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $amount = (float) $request->amount;
        $customer->balance = max(0, (float) $customer->balance + $amount);
        $customer->save();

        $actionText = $amount >= 0 ? 'eklendi' : 'düşüldü';
        return redirect()->route('admin.customers.index')
            ->with('success', "{$customer->name} isimli müşteriye ₺" . number_format(abs($amount), 2) . " bakiye {$actionText}. Güncel Bakiye: ₺" . number_format($customer->balance, 2));
    }

    public function updatePassword(Request $request, User $customer)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $customer->password = Hash::make($request->password);
        $customer->save();

        return redirect()->route('admin.customers.index')
            ->with('success', "{$customer->name} müşterisinin şifresi başarıyla güncellendi.");
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

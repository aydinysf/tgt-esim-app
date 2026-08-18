<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BranchController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isBranchUser()) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Şube personelleri şube yönetim alanına erişemez.');
        }

        $branches = Branch::where('user_id', $user->id)
            ->withCount(['orders', 'staff'])
            ->with('staff')
            ->latest()
            ->get();

        return view('customer.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isBranchUser()) {
            return back()->with('error', 'Yetkisiz işlem!');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = $user->id;
        $validated['is_active'] = true;

        Branch::create($validated);

        return back()->with('success', 'Yeni şube başarıyla eklendi.');
    }

    public function storeStaff(Request $request, Branch $branch)
    {
        $user = Auth::user();
        if ($branch->user_id !== $user->id) {
            return back()->with('error', 'Yetkisiz işlem!');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'branch',
            'parent_id' => $user->id,
            'branch_id' => $branch->id,
            'company_name' => $user->company_name . ' - ' . $branch->name,
        ]);

        return back()->with('success', "'{$branch->name}' şubesi için personel hesabı tanımlandı.");
    }

    public function destroyStaff(User $staff)
    {
        $user = Auth::user();
        if ($staff->parent_id !== $user->id) {
            return back()->with('error', 'Yetkisiz işlem!');
        }

        $staff->delete();

        return back()->with('success', 'Şube personel hesabı silindi.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->user_id !== Auth::id()) {
            return back()->with('error', 'Yetkisiz işlem!');
        }

        $branch->delete();

        return back()->with('success', 'Şube silindi.');
    }
}

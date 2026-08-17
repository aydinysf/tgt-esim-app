<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::where('user_id', Auth::id())
            ->withCount('orders')
            ->latest()
            ->get();

        return view('customer.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_active'] = true;

        Branch::create($validated);

        return back()->with('success', 'Yeni şube başarıyla eklendi.');
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

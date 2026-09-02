<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Strictly protect against branch staff access
        if ($user->isBranchUser()) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Şube personelleri satış ve ciro raporlarına erişemez.');
        }

        $effectiveCustomer = $user->getEffectiveCustomer();

        // Filters
        $selectedBranchId = $request->query('branch_id');
        $datePreset = $request->query('preset', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Base query for this dealer
        $baseQuery = Order::where('user_id', $effectiveCustomer->id);

        if ($selectedBranchId) {
            $baseQuery->where('branch_id', $selectedBranchId);
        }

        // Apply Date Presets
        if ($datePreset === 'today') {
            $baseQuery->whereDate('created_at', Carbon::today());
        } elseif ($datePreset === 'yesterday') {
            $baseQuery->whereDate('created_at', Carbon::yesterday());
        } elseif ($datePreset === 'this_week') {
            $baseQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($datePreset === 'this_month') {
            $baseQuery->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($datePreset === 'last_month') {
            $baseQuery->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
        } elseif ($dateFrom && $dateTo) {
            $baseQuery->whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ]);
        }

        // Global Metrics for Cards
        $allOrdersQuery = Order::where('user_id', $effectiveCustomer->id);
        if ($selectedBranchId) {
            $allOrdersQuery->where('branch_id', $selectedBranchId);
        }

        $totalRevenue = (float) (clone $baseQuery)->sum('sale_price');
        $totalOrders = (int) (clone $baseQuery)->count();

        // Today and Month metrics
        $todayRevenue = (float) (clone $allOrdersQuery)->whereDate('created_at', Carbon::today())->sum('sale_price');
        $todayOrders = (int) (clone $allOrdersQuery)->whereDate('created_at', Carbon::today())->count();

        $monthRevenue = (float) (clone $allOrdersQuery)->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('sale_price');
        $monthOrders = (int) (clone $allOrdersQuery)->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();

        // Branch Breakdown
        $branchBreakdown = Order::select(
            'branch_id',
            'branch_name',
            DB::raw('count(*) as total_sales'),
            DB::raw('sum(sale_price) as total_revenue')
        )
        ->where('user_id', $effectiveCustomer->id)
        ->groupBy('branch_id', 'branch_name')
        ->orderByDesc('total_revenue')
        ->get();

        // Top Sold Packages Breakdown
        $packageBreakdown = (clone $baseQuery)->select(
            'tgt_product_id',
            DB::raw('count(*) as total_sales'),
            DB::raw('sum(sale_price) as total_revenue')
        )
        ->groupBy('tgt_product_id')
        ->with('product')
        ->orderByDesc('total_sales')
        ->limit(10)
        ->get();

        // Detailed Orders List
        $orders = (clone $baseQuery)->with(['product', 'branch'])->latest()->paginate(25)->withQueryString();

        // Branches list for filter dropdown
        $branches = Branch::where('user_id', $effectiveCustomer->id)->where('is_active', true)->get();

        return view('customer.reports.index', compact(
            'orders',
            'branches',
            'totalRevenue',
            'totalOrders',
            'todayRevenue',
            'todayOrders',
            'monthRevenue',
            'monthOrders',
            'branchBreakdown',
            'packageBreakdown',
            'selectedBranchId',
            'datePreset',
            'dateFrom',
            'dateTo',
            'effectiveCustomer'
        ));
    }
}

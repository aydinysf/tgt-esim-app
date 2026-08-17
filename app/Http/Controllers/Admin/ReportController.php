<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TgtProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedCustomerId = $request->query('customer_id');
        $selectedBranchId = $request->query('branch_id');

        $query = Order::with(['customer', 'branch', 'product'])->latest();

        if ($selectedCustomerId) {
            $query->where('user_id', $selectedCustomerId);
        }

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        $orders = $query->paginate(20);

        // Overall Sales & Profit Breakdown by Package
        $productStatsQuery = Order::select(
            'tgt_product_id',
            DB::raw('count(*) as total_sales'),
            DB::raw('sum(sale_price) as total_revenue'),
            DB::raw('sum(net_price) as total_cost'),
            DB::raw('sum(profit) as total_profit')
        );

        if ($selectedCustomerId) {
            $productStatsQuery->where('user_id', $selectedCustomerId);
        }
        if ($selectedBranchId) {
            $productStatsQuery->where('branch_id', $selectedBranchId);
        }

        $productStats = $productStatsQuery->groupBy('tgt_product_id')
            ->with('product')
            ->get();

        $totalRevenueQuery = Order::query();
        $totalCostQuery = Order::query();
        $totalProfitQuery = Order::query();

        if ($selectedCustomerId) {
            $totalRevenueQuery->where('user_id', $selectedCustomerId);
            $totalCostQuery->where('user_id', $selectedCustomerId);
            $totalProfitQuery->where('user_id', $selectedCustomerId);
        }
        if ($selectedBranchId) {
            $totalRevenueQuery->where('branch_id', $selectedBranchId);
            $totalCostQuery->where('branch_id', $selectedBranchId);
            $totalProfitQuery->where('branch_id', $selectedBranchId);
        }

        $totalRevenue = $totalRevenueQuery->sum('sale_price');
        $totalCost = $totalCostQuery->sum('net_price');
        $totalProfit = $totalProfitQuery->sum('profit');

        $customers = User::where('role', 'customer')->with('branches')->get();

        $availableBranches = collect();
        if ($selectedCustomerId) {
            $customer = $customers->firstWhere('id', $selectedCustomerId);
            if ($customer) {
                $availableBranches = $customer->branches;
            }
        }

        return view('admin.reports.index', compact(
            'orders',
            'productStats',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'customers',
            'selectedCustomerId',
            'selectedBranchId',
            'availableBranches'
        ));
    }
}

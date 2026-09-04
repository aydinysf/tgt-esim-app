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
        $search = trim((string) $request->query('search', ''));

        $query = Order::with(['customer', 'branch', 'product'])->latest();

        if ($selectedCustomerId) {
            $query->where('user_id', $selectedCustomerId);
        }

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhere('channel_order_no', 'like', "%{$search}%")
                  ->orWhere('iccid', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('product_name', 'like', "%{$search}%")
                         ->orWhere('product_code', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

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
            'availableBranches',
            'search'
        ));
    }

    public function liveStatus(Order $order, \App\Services\TgtEsimService $tgtService)
    {
        $usage = $order->order_no ? $tgtService->getOrderUsage($order->order_no) : [];
        $profile = $order->iccid ? $tgtService->getProfileInfo($order->iccid) : [];

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_no' => $order->order_no ?? $order->channel_order_no,
                'customer' => $order->customer->name ?? 'Müşteri',
                'branch' => $order->branch_name ?? 'Merkez / Genel',
                'product' => $order->product->product_name ?? 'eSIM',
                'iccid' => $order->iccid,
                'qr_code' => $order->qr_code,
                'apple_install_url' => $order->apple_install_url,
                'created_at' => $order->created_at->format('d.m.Y H:i'),
            ],
            'usage' => $usage,
            'profile' => $profile,
        ]);
    }
}

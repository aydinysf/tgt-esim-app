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

        $query = Order::with(['customer', 'product'])->latest();

        if ($selectedCustomerId) {
            $query->where('user_id', $selectedCustomerId);
        }

        $orders = $query->paginate(20);

        // Overall Sales & Profit Breakdown by Package
        $productStats = Order::select(
            'tgt_product_id',
            DB::raw('count(*) as total_sales'),
            DB::raw('sum(sale_price) as total_revenue'),
            DB::raw('sum(net_price) as total_cost'),
            DB::raw('sum(profit) as total_profit')
        )
        ->groupBy('tgt_product_id')
        ->with('product')
        ->get();

        $totalRevenue = Order::sum('sale_price');
        $totalCost = Order::sum('net_price');
        $totalProfit = Order::sum('profit');

        $customers = User::where('role', 'customer')->get();

        return view('admin.reports.index', compact(
            'orders',
            'productStats',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'customers',
            'selectedCustomerId'
        ));
    }
}

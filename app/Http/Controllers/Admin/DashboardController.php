<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TgtProduct;
use App\Models\Order;
use App\Services\TgtEsimService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(TgtEsimService $tgtService)
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = TgtProduct::count();
        $totalOrders = Order::count();
        
        $totalRevenue = Order::sum('sale_price');
        $totalCost = Order::sum('net_price');
        $totalProfit = Order::sum('profit');

        $recentOrders = Order::with(['customer', 'product'])
            ->latest()
            ->take(5)
            ->get();

        $accountBalance = $tgtService->getAccountBalance();

        return view('admin.dashboard', compact(
            'totalCustomers',
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'recentOrders',
            'accountBalance'
        ));
    }
}

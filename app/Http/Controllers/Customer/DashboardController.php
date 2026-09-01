<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Services\TgtEsimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        // Selected branch filter (for dealer admin)
        $selectedBranchId = $request->query('branch_id');

        // Packages assigned to effective customer by Admin
        $assignedPackages = CustomerPackage::where('user_id', $effectiveCustomer->id)
            ->where('is_active', true)
            ->with('product')
            ->get();

        // Branches defined by effective customer
        $branches = $effectiveCustomer->branches()->where('is_active', true)->get();

        // Orders query
        $ordersQuery = Order::where('user_id', $effectiveCustomer->id)
            ->with(['product', 'branch']);

        // If user is a branch staff, filter only their branch orders!
        if ($user->isBranchUser() && $user->branch_id) {
            $ordersQuery->where('branch_id', $user->branch_id);
        } elseif ($selectedBranchId) {
            $ordersQuery->where('branch_id', $selectedBranchId);
        }

        $orders = $ordersQuery->latest()->get();

        // Calculate sales summary per branch for dealer admin
        $branchStats = Order::select(
            'branch_id',
            'branch_name',
            DB::raw('count(*) as total_orders'),
            DB::raw('sum(sale_price) as total_spent')
        )
        ->where('user_id', $effectiveCustomer->id)
        ->groupBy('branch_id', 'branch_name')
        ->get();

        return view('customer.dashboard', compact(
            'assignedPackages',
            'orders',
            'branches',
            'effectiveCustomer',
            'selectedBranchId',
            'branchStats'
        ));
    }

    public function buyPackage(Request $request, TgtEsimService $tgtService)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        $validated = $request->validate([
            'customer_package_id' => 'required|exists:customer_packages,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $assignment = CustomerPackage::where('id', $validated['customer_package_id'])
            ->where('user_id', $effectiveCustomer->id)
            ->with('product')
            ->firstOrFail();

        $branch = null;

        // If user is branch staff, locked to their assigned branch!
        if ($user->isBranchUser() && $user->branch) {
            $branch = $user->branch;
        } elseif (!empty($validated['branch_id'])) {
            $branch = $effectiveCustomer->branches()->where('id', $validated['branch_id'])->first();
        }

        $product = $assignment->product;
        $salePrice = (float) $assignment->sale_price;

        // Check if effective customer has enough credit balance
        if (!$effectiveCustomer->hasBalance($salePrice)) {
            return back()->with('error', 'Yetersiz Bakiye! Bu paket için €' . number_format($salePrice, 2) . ' bakiye gereklidir. Mevcut Bakiyeniz: €' . number_format($effectiveCustomer->balance, 2));
        }

        $channelOrderNo = 'TGT-' . date('Ymd') . '-' . Str::random(6);
        $idempotencyKey = (string) Str::uuid();

        // Call TGT API to place order
        $apiResult = $tgtService->createOrder(
            $product->product_code,
            $channelOrderNo,
            $idempotencyKey,
            $user->email
        );

        if (!$apiResult['success']) {
            return back()->with('error', 'TGT API Sipariş Hatası: ' . ($apiResult['msg'] ?? 'Bilinmeyen Hata'));
        }

        // Deduct balance from effective customer account
        $effectiveCustomer->deductBalance($salePrice);

        $netPriceUsd = (float) $product->net_price;
        $netPriceEur = \App\Services\CurrencyService::convertUsdToEur($netPriceUsd);
        $profit = round($salePrice - $netPriceEur, 2);

        $order = Order::create([
            'order_no' => $apiResult['orderNo'],
            'channel_order_no' => $channelOrderNo,
            'user_id' => $effectiveCustomer->id,
            'branch_id' => $branch?->id,
            'branch_name' => $branch?->name ?? 'Merkez / Genel',
            'tgt_product_id' => $product->id,
            'net_price' => $netPriceEur,
            'sale_price' => $salePrice,
            'profit' => $profit,
            'iccid' => $apiResult['iccid'],
            'qr_code' => $apiResult['qrCode'],
            'order_status' => 'ACTIVATED',
            'profile_status' => 'activated',
            'idempotency_key' => $idempotencyKey,
            'raw_response' => $apiResult['raw'] ?? [],
        ]);

        // Send Email Notification to Customer and Staff
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\EsimPurchasedMail($order));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('eSIM Email sending failed: ' . $e->getMessage());
        }

        return redirect()->route('customer.dashboard')
            ->with('success', "{$product->product_name} paketiniz €" . number_format($salePrice, 2) . " bakiyeniz düşülerek (" . ($branch?->name ?? 'Merkez') . " adına) başarıyla satın alındı ve QR kodunuz hazırlandı! Kalan Bakiyeniz: €" . number_format($effectiveCustomer->balance, 2));
    }

    public function getUsageInfo(Order $order, TgtEsimService $tgtService)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        if ($order->user_id !== $effectiveCustomer->id) {
            return response()->json(['error' => 'Yetkisiz erişim'], 403);
        }

        $usage = $tgtService->getOrderUsage($order->order_no ?? '');
        $profile = $order->iccid ? $tgtService->getProfileInfo($order->iccid) : [];

        return response()->json([
            'success' => true,
            'order' => $order,
            'usage' => $usage,
            'profile' => $profile,
            'appleUrl' => $order->apple_install_url,
        ]);
    }
}

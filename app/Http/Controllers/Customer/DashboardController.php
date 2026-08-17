<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Services\TgtEsimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Packages assigned to this customer by Admin
        $assignedPackages = CustomerPackage::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('product')
            ->get();

        // Branches defined by this customer
        $branches = $user->branches()->where('is_active', true)->get();

        // Orders purchased by this customer
        $orders = Order::where('user_id', $user->id)
            ->with(['product', 'branch'])
            ->latest()
            ->get();

        return view('customer.dashboard', compact('assignedPackages', 'orders', 'branches'));
    }

    public function buyPackage(Request $request, TgtEsimService $tgtService)
    {
        $validated = $request->validate([
            'customer_package_id' => 'required|exists:customer_packages,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = Auth::user();
        $assignment = CustomerPackage::where('id', $validated['customer_package_id'])
            ->where('user_id', $user->id)
            ->with('product')
            ->firstOrFail();

        $branch = null;
        if (!empty($validated['branch_id'])) {
            $branch = $user->branches()->where('id', $validated['branch_id'])->first();
        }

        $product = $assignment->product;
        $salePrice = (float) $assignment->sale_price;

        // Check if customer has enough credit balance
        if (!$user->hasBalance($salePrice)) {
            return back()->with('error', 'Yetersiz Bakiye! Bu paket için ₺' . number_format($salePrice, 2) . ' bakiye gereklidir. Mevcut Bakiyeniz: ₺' . number_format($user->balance, 2));
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
            return back()->with('error', 'TGT API Sipariş oluşturulamadı: ' . ($apiResult['msg'] ?? 'Bilinmeyen Hata'));
        }

        // Deduct balance from customer account
        $user->deductBalance($salePrice);

        $netPrice = (float) $product->net_price;
        $profit = $salePrice - $netPrice;

        $order = Order::create([
            'order_no' => $apiResult['orderNo'],
            'channel_order_no' => $channelOrderNo,
            'user_id' => $user->id,
            'branch_id' => $branch?->id,
            'branch_name' => $branch?->name ?? 'Merkez / Genel',
            'tgt_product_id' => $product->id,
            'net_price' => $netPrice,
            'sale_price' => $salePrice,
            'profit' => $profit,
            'iccid' => $apiResult['iccid'],
            'qr_code' => $apiResult['qrCode'],
            'order_status' => 'ACTIVATED',
            'profile_status' => 'activated',
            'idempotency_key' => $idempotencyKey,
            'raw_response' => $apiResult['raw'] ?? [],
        ]);

        // Send Email Notification to Customer
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\EsimPurchasedMail($order));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('eSIM Email sending failed: ' . $e->getMessage());
        }

        return redirect()->route('customer.dashboard')
            ->with('success', "{$product->product_name} paketiniz ₺" . number_format($salePrice, 2) . " bakiyeniz düşülerek başarıyla satın alındı ve QR kodunuz e-posta olarak gönderildi! Kalan Bakiyeniz: ₺" . number_format($user->balance, 2));
    }

    public function getUsageInfo(Order $order, TgtEsimService $tgtService)
    {
        if ($order->user_id !== Auth::id()) {
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

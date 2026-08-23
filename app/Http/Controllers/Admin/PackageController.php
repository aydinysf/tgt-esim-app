<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TgtProduct;
use App\Models\User;
use App\Models\CustomerPackage;
use App\Services\TgtEsimService;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $products = TgtProduct::withCount('customerPackages')->latest()->get();
        $customers = User::where('role', 'customer')->get();

        return view('admin.packages.index', compact('products', 'customers'));
    }

    public function sync(Request $request, TgtEsimService $tgtService)
    {
        $filters = [
            'countryCode' => $request->input('country_code'),
            'productType' => $request->input('product_type'),
            'usagePeriod' => $request->input('usage_period'),
        ];

        $apiProducts = $tgtService->getProducts($filters, 1, 100);

        $newCount = 0;
        $updatedCount = 0;

        foreach ($apiProducts as $p) {
            $productCode = $p['productCode'] ?? null;
            if (!$productCode) {
                continue;
            }

            $existing = TgtProduct::where('product_code', $productCode)->first();

            TgtProduct::updateOrCreate(
                ['product_code' => $productCode],
                [
                    'product_name' => $p['productName'] ?? 'eSIM Package',
                    'product_type' => $p['productType'] ?? 'DATA_PACK',
                    'country_code_list' => $p['countryCodeList'] ?? [],
                    'mcc_list' => $p['mccList'] ?? [],
                    'net_price' => $p['netPrice'] ?? 0.00,
                    'data_total' => $p['dataTotal'] ?? 1,
                    'data_unit' => $p['dataUnit'] ?? 'GB',
                    'usage_period' => $p['usagePeriod'] ?? 1,
                    'validity_period' => $p['validityPeriod'] ?? 30,
                    'card_type' => $p['cardType'] ?? null,
                    'raw_data' => $p,
                ]
            );

            if ($existing) {
                $updatedCount++;
            } else {
                $newCount++;
            }
        }

        $totalProducts = TgtProduct::count();
        $filterSummary = [];
        if (!empty($filters['countryCode'])) $filterSummary[] = "Ülke: " . strtoupper($filters['countryCode']);
        if (!empty($filters['productType'])) $filterSummary[] = "Tip: " . ($filters['productType'] === 'DATA_PACK' ? 'Sabit Veri' : 'Günlük');
        if (!empty($filters['usagePeriod'])) $filterSummary[] = "Süre: " . $filters['usagePeriod'] . " Gün";
        
        $filterText = count($filterSummary) > 0 ? " [" . implode(', ', $filterSummary) . "]" : "";

        return redirect()->route('admin.packages.index')
            ->with('success', "TGT API senkronizasyonu{$filterText} tamamlandı. ({$newCount} yeni paket eklendi, {$updatedCount} paket güncellendi. Kataloğunuzda Toplam: {$totalProducts} aktif paket)");
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'target_customers' => 'required', // 'all' or array of user_ids
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'target_products' => 'required', // 'all' or array of product_ids
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:tgt_products,id',
            'pricing_type' => 'required|in:fixed,margin_percent,margin_fixed',
            'price_value' => 'required|numeric|min:0',
        ]);

        // Resolve Target Customers
        if ($validated['target_customers'] === 'all') {
            $customerIds = User::where('role', 'customer')->pluck('id')->toArray();
        } else {
            $customerIds = $validated['user_ids'] ?? [];
        }

        if (empty($customerIds)) {
            return back()->with('error', 'Lütfen en az bir müşteri seçin veya "Tüm Müşteriler" seçeneğini işaretleyin.');
        }

        // Resolve Target Products
        if ($validated['target_products'] === 'all') {
            $products = TgtProduct::all();
        } else {
            $productIds = $validated['product_ids'] ?? [];
            $products = TgtProduct::whereIn('id', $productIds)->get();
        }

        if ($products->isEmpty()) {
            return back()->with('error', 'Lütfen en az bir paket seçin veya "Tüm Paketler" seçeneğini işaretleyin.');
        }

        $pricingType = $validated['pricing_type'];
        $priceValue = (float) $validated['price_value'];
        $assignedCount = 0;

        foreach ($customerIds as $userId) {
            foreach ($products as $product) {
                $netPrice = (float) $product->net_price;
                
                // Calculate custom selling price based on pricing rule
                if ($pricingType === 'margin_percent') {
                    $salePrice = round($netPrice * (1 + ($priceValue / 100)), 2);
                } elseif ($pricingType === 'margin_fixed') {
                    $salePrice = round($netPrice + $priceValue, 2);
                } else { // fixed
                    $salePrice = round($priceValue, 2);
                }

                if ($salePrice <= 0) {
                    $salePrice = max(0.01, $netPrice);
                }

                CustomerPackage::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'tgt_product_id' => $product->id,
                    ],
                    [
                        'sale_price' => $salePrice,
                        'is_active' => true,
                    ]
                );

                $assignedCount++;
            }
        }

        $customerCount = count($customerIds);
        $productCount = $products->count();

        return back()->with('success', "Toplu paket ataması tamamlandı! Toplam {$productCount} adet paket, {$customerCount} farklı müşteriye (Toplam {$assignedCount} atama) başarıyla tanımlandı.");
    }

    public function removeAssignment(CustomerPackage $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Paket ataması kaldırıldı.');
    }
}

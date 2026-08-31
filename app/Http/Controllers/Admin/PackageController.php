<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TgtProduct;
use App\Models\User;
use App\Models\CustomerPackage;
use App\Services\TgtEsimService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $perPage   = in_array((int) $request->input('per_page', 25), [10, 25, 50, 100, 250]) ? (int) $request->input('per_page', 25) : 25;
        $search    = $request->input('search');
        $country   = $request->input('country');
        $type      = $request->input('type');
        $period    = $request->input('period');

        $query = TgtProduct::withCount('customerPackages')->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($country) {
            $query->whereJsonContains('country_code_list', strtoupper($country));
        }

        if ($type) {
            $query->where('product_type', $type);
        }

        if ($period) {
            $query->where('usage_period', (int) $period);
        }

        $products      = $query->paginate($perPage)->withQueryString();
        $customers     = User::where('role', 'customer')->get();
        $exchangeRates = CurrencyService::getRates();
        $usdToEurRate  = CurrencyService::getUsdToEurRate();

        return view('admin.packages.index', compact('products', 'customers', 'perPage', 'search', 'country', 'type', 'period', 'exchangeRates', 'usdToEurRate'));
    }

    /**
     * AJAX: return all products as JSON for assignment checkboxes (lightweight) with TCMB converted EUR prices
     */
    public function productsJson()
    {
        $usdToEurRate = CurrencyService::getUsdToEurRate();

        $products = TgtProduct::select('id', 'product_name', 'net_price')
            ->orderBy('product_name')
            ->get()
            ->map(function ($p) use ($usdToEurRate) {
                return [
                    'id' => $p->id,
                    'product_name' => $p->product_name,
                    'net_price_usd' => (float) $p->net_price,
                    'net_price_eur' => round((float) $p->net_price * $usdToEurRate, 2),
                ];
            });

        return response()->json([
            'products' => $products,
            'usd_to_eur_rate' => $usdToEurRate,
        ]);
    }

    public function sync(Request $request, TgtEsimService $tgtService)
    {
        // Increase execution time and memory for large API syncs (e.g. 11,000+ packages)
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $filters = [
            'countryCode' => $request->input('country_code'),
            'productType' => $request->input('product_type'),
            'usagePeriod' => $request->input('usage_period'),
            'cardType' => $request->input('card_type'),
            'productName' => $request->input('product_name'),
            'maxPages' => $request->input('max_pages', 5),
        ];

        $maxPages = (int) $filters['maxPages'];
        $apiProducts = $tgtService->getProducts($filters, $maxPages, 100);

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

    public function syncChunked(Request $request, TgtEsimService $tgtService)
    {
        $page = (int) $request->input('page', 1);
        $maxPages = (int) $request->input('max_pages', 5);
        $countryCodeFilter = $request->input('country_code');

        $filters = [
            'countryCode' => $countryCodeFilter,
            'productType' => $request->input('product_type'),
            'usagePeriod' => $request->input('usage_period'),
            'cardType' => $request->input('card_type'),
            'productName' => $request->input('product_name'),
        ];

        // Fetch exactly 1 page
        $result = $tgtService->getProductsPage($filters, $page, 100);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => "Sayfa {$page} çekilirken TGT API hatası oluştu."
            ]);
        }

        $items = $result['items'];
        $savedCount = 0;

        foreach ($items as $p) {
            $productCode = $p['productCode'] ?? null;
            if (!$productCode) continue;

            // Local fallback filter if API ignored the countryCode
            $countryList = $p['countryCodeList'] ?? [];
            if (!is_array($countryList)) {
                // If the API returns a string like "TR,US", split it
                $countryList = is_string($countryList) ? explode(',', $countryList) : (array)$countryList;
            }
            $countryList = array_map('strtoupper', array_map('trim', $countryList));

            if (!empty($countryCodeFilter)) {
                $cc = strtoupper(trim($countryCodeFilter));
                if (!in_array($cc, $countryList)) {
                    continue; // Skip if country doesn't match
                }
            }

            TgtProduct::updateOrCreate(
                ['product_code' => $productCode],
                [
                    'product_name' => $p['productName'] ?? 'eSIM Package',
                    'product_type' => $p['productType'] ?? 'DATA_PACK',
                    'country_code_list' => $countryList,
                    'mcc_list' => $p['mccList'] ?? [],
                    'net_price' => $p['netPrice'] ?? 0.00,
                    'data_total' => $p['dataTotal'] ?? 1,
                    'data_unit' => $p['dataUnit'] ?? 'GB',
                    'usage_period' => $p['usagePeriod'] ?? 1,
                    'validity_period' => $p['validityPeriod'] ?? 0,
                    'card_type' => $p['cardType'] ?? null,
                    'raw_data' => $p,
                ]
            );
            $savedCount++;
        }

        $hasMore = $result['hasMore'];
        if ($page >= $maxPages) {
            $hasMore = false; // Stop if we hit the requested limit
        }

        return response()->json([
            'success' => true,
            'page' => $page,
            'saved_count' => $savedCount,
            'has_more' => $hasMore,
            'next_page' => $hasMore ? $page + 1 : null
        ]);
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
        $usdToEurRate = CurrencyService::getUsdToEurRate();

        foreach ($customerIds as $userId) {
            foreach ($products as $product) {
                $netPriceUsd = (float) $product->net_price;
                // Convert USD net price from TGT to EUR via live TCMB rate
                $netPriceEur = round($netPriceUsd * $usdToEurRate, 2);
                
                // Calculate custom selling price in EUR based on pricing rule
                if ($pricingType === 'margin_percent') {
                    $salePrice = round($netPriceEur * (1 + ($priceValue / 100)), 2);
                } elseif ($pricingType === 'margin_fixed') {
                    $salePrice = round($netPriceEur + $priceValue, 2);
                } else { // fixed
                    $salePrice = round($priceValue, 2);
                }

                if ($salePrice <= 0) {
                    $salePrice = max(0.01, $netPriceEur);
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

    public function destroy(TgtProduct $product)
    {
        $productName = $product->product_name;
        $product->delete();
        return back()->with('success', "{$productName} isimli paket başarıyla silindi.");
    }
}

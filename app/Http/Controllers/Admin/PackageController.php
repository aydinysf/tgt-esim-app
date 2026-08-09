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

    public function sync(TgtEsimService $tgtService)
    {
        $apiProducts = $tgtService->getProducts(1, 100);

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

        return redirect()->route('admin.packages.index')
            ->with('success', "TGT API / Datasheet senkronizasyonu tamamlandı. ({$newCount} yeni paket eklendi, {$updatedCount} paket güncellendi. Toplam: {$totalProducts} aktif paket)");
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgt_product_id' => 'required|exists:tgt_products,id',
            'sale_price' => 'required|numeric|min:0.01',
        ]);

        $product = TgtProduct::findOrFail($validated['tgt_product_id']);

        CustomerPackage::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'tgt_product_id' => $validated['tgt_product_id'],
            ],
            [
                'sale_price' => $validated['sale_price'],
                'is_active' => true,
            ]
        );

        $customer = User::find($validated['user_id']);

        return back()->with('success', "{$product->product_name} paketi {$customer->name} müşterisine ₺{$validated['sale_price']} fiyatla atandı.");
    }

    public function removeAssignment(CustomerPackage $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Paket ataması kaldırıldı.');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\IwcProduct;
use App\Services\IwcService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncIwcProductsCommand extends Command
{
    protected $signature = 'iwc:sync-products {--seed-from-json : Seed from collection JSON if API is offline}';
    protected $description = 'Sync products and packages from Istanbul Welcome Card (IWC) Partner API';

    public function handle(IwcService $iwcService): int
    {
        $this->info('Starting IWC Products synchronization...');

        if ($iwcService->isConfigured()) {
            $this->info('Fetching products from live IWC API...');
            $result = $iwcService->getProducts(now()->format('Y-m-d'), now()->addMonths(3)->format('Y-m-d'));

            if (!empty($result['status']) && !empty($result['data']['products'])) {
                $count = 0;
                foreach ($result['data']['products'] as $pData) {
                    $this->saveProduct($pData);
                    $count++;
                }
                $this->info("Successfully synced {$count} products from IWC API.");
                return self::SUCCESS;
            }

            $this->warn('IWC API returned no products or an error: ' . ($result['message'] ?? 'Unknown error'));
        } else {
            $this->warn('IWC API credentials (IWC_PROFILE_ID, IWC_ACCESS_KEY) are not set in .env.');
        }

        // Fallback / Seed from IWC-Partner-API-V2-Collection.json if requested or if DB is empty
        if ($this->option('seed-from-json') || IwcProduct::count() === 0) {
            $this->info('Checking local IWC collection sample data to populate initial products...');
            $jsonPath = base_path('IWC-Partner-API-V2-Collection.json');
            if (File::exists($jsonPath)) {
                $collection = json_decode(File::get($jsonPath), true);
                $seeded = 0;

                // Look for saved responses in the collection
                if (!empty($collection['item'])) {
                    foreach ($collection['item'] as $folder) {
                        if (($folder['name'] ?? '') === 'Products' && !empty($folder['item'])) {
                            foreach ($folder['item'] as $req) {
                                if (!empty($req['response'])) {
                                    foreach ($req['response'] as $resp) {
                                        $body = json_decode($resp['body'] ?? '{}', true);
                                        if (!empty($body['data']['id'])) {
                                            $this->saveProduct($body['data']);
                                            $seeded++;
                                        } elseif (!empty($body['data']['products'])) {
                                            foreach ($body['data']['products'] as $p) {
                                                $this->saveProduct($p);
                                                $seeded++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($seeded > 0) {
                    $this->info("Seeded {$seeded} sample product(s) from collection JSON.");
                    return self::SUCCESS;
                }
            }
        }

        return self::SUCCESS;
    }

    protected function saveProduct(array $p): void
    {
        $id = $p['id'];
        $name = is_array($p['name'] ?? null) ? ($p['name']['tr'] ?? $p['name']['en'] ?? reset($p['name'])) : ($p['name'] ?? 'IWC Ürünü');
        $slug = is_array($p['slug'] ?? null) ? ($p['slug']['tr'] ?? $p['slug']['en'] ?? reset($p['slug'])) : ($p['slug'] ?? null);
        $desc = is_array($p['description'] ?? null) ? ($p['description']['tr'] ?? $p['description']['en'] ?? reset($p['description'])) : ($p['description'] ?? null);

        $price = $p['price']['amount'] ?? 0;
        $regularPrice = $p['price']['regular_amount'] ?? $price;
        $currency = $p['price']['currency'] ?? 'EUR';

        IwcProduct::updateOrCreate(
            ['id' => $id],
            [
                'type' => $p['type'] ?? 'ticket',
                'name' => $name,
                'slug' => $slug,
                'description' => $desc,
                'short_description' => $p['short_description'] ?? null,
                'price' => $price,
                'regular_price' => $regularPrice,
                'currency' => $currency,
                'status' => $p['status'] ?? 'Available',
                'availability_type' => $p['availability_type'] ?? 'time_point',
                'variants_data' => $p['variants'] ?? [],
                'contents_data' => $p['contents'] ?? [],
                'required_order_data' => $p['required_order_data'] ?? [],
                'raw_data' => $p,
                'last_synced_at' => now(),
            ]
        );
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    const CACHE_KEY = 'tcmb_exchange_rates';
    const CACHE_TTL_MINUTES = 120; // 2 hours
    const DEFAULT_USD_TO_EUR = 0.92;

    /**
     * Get USD to EUR exchange rate from TCMB (Central Bank of the Republic of Turkey)
     *
     * @return float
     */
    public static function getUsdToEurRate(): float
    {
        $details = self::getRates();
        return (float) ($details['usd_to_eur'] ?? self::DEFAULT_USD_TO_EUR);
    }

    /**
     * Convert USD amount to EUR
     *
     * @param float $amountInUsd
     * @return float
     */
    public static function convertUsdToEur(float $amountInUsd): float
    {
        $rate = self::getUsdToEurRate();
        return round($amountInUsd * $rate, 2);
    }

    /**
     * Fetch and cache exchange rate details from TCMB
     *
     * @return array
     */
    public static function getRates(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(6)
                    ->get('https://www.tcmb.gov.tr/kurlar/today.xml');

                if ($response->successful()) {
                    $xmlString = $response->body();
                    $xml = simplexml_load_string($xmlString);

                    if ($xml !== false) {
                        $usdSelling = 0.0;
                        $eurSelling = 0.0;
                        $crossEur = 0.0;

                        foreach ($xml->Currency as $currency) {
                            $code = (string) $currency['Kod'];
                            if ($code === 'USD') {
                                $usdSelling = (float) $currency->ForexSelling;
                            } elseif ($code === 'EUR') {
                                $eurSelling = (float) $currency->ForexSelling;
                                $crossEur = (float) $currency->CrossRateOther;
                            }
                        }

                        $rate = self::DEFAULT_USD_TO_EUR;
                        if ($crossEur > 0) {
                            // TCMB CrossRateOther for EUR is 1 EUR = X USD, so 1 USD = 1 / X EUR
                            $rate = round(1 / $crossEur, 4);
                        } elseif ($usdSelling > 0 && $eurSelling > 0) {
                            $rate = round($usdSelling / $eurSelling, 4);
                        }

                        return [
                            'usd_to_eur' => $rate,
                            'usd_try' => $usdSelling,
                            'eur_try' => $eurSelling,
                            'date' => (string) ($xml['Tarih'] ?? date('d.m.Y')),
                            'bulletin_no' => (string) ($xml['Bulten_No'] ?? ''),
                            'updated_at' => now()->format('d.m.Y H:i'),
                            'source' => 'TCMB',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('TCMB Exchange Rate fetch error: ' . $e->getMessage());
            }

            return [
                'usd_to_eur' => self::DEFAULT_USD_TO_EUR,
                'usd_try' => 0,
                'eur_try' => 0,
                'date' => date('d.m.Y'),
                'updated_at' => now()->format('d.m.Y H:i'),
                'source' => 'Fallback',
            ];
        });
    }
}

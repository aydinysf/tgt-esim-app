<?php

namespace App\Services;

use App\Models\TgtProduct;
use Illuminate\Support\Facades\Log;

class TgtPriceListImportService
{
    protected array $countryMap = [
        'turkey' => 'TR', 'türkiye' => 'TR',
        'japan' => 'JP', 'japonya' => 'JP',
        'germany' => 'DE', 'almanya' => 'DE',
        'france' => 'FR', 'fransa' => 'FR',
        'united states' => 'US', 'usa' => 'US', 'abd' => 'US',
        'united kingdom' => 'GB', 'uk' => 'GB', 'ingiltere' => 'GB',
        'italy' => 'IT', 'italya' => 'IT',
        'spain' => 'ES', 'ispanya' => 'ES',
        'austria' => 'AT', 'avusturya' => 'AT',
        'belgium' => 'BE', 'belçika' => 'BE',
        'bulgaria' => 'BG', 'bulgaristan' => 'BG',
        'croatia' => 'HR', 'hırvatistan' => 'HR',
        'cyprus' => 'CY', 'kıbrıs' => 'CY',
        'czechia' => 'CZ', 'czech republic' => 'CZ', 'çekya' => 'CZ',
        'denmark' => 'DK', 'danimarka' => 'DK',
        'estonia' => 'EE', 'estonya' => 'EE',
        'finland' => 'FI', 'finlandiya' => 'FI',
        'greece' => 'GR', 'yunanistan' => 'GR',
        'hungary' => 'HU', 'macaristan' => 'HU',
        'iceland' => 'IS', 'izlanda' => 'IS',
        'ireland' => 'IE', 'irlanda' => 'IE',
        'latvia' => 'LV', 'letonya' => 'LV',
        'lithuania' => 'LT', 'litvanya' => 'LT',
        'luxembourg' => 'LU', 'lüksemburg' => 'LU',
        'malta' => 'MT',
        'netherlands' => 'NL', 'hollanda' => 'NL',
        'norway' => 'NO', 'norveç' => 'NO',
        'poland' => 'PL', 'polonya' => 'PL',
        'portugal' => 'PT', 'portekiz' => 'PT',
        'romania' => 'RO', 'romanya' => 'RO',
        'slovakia' => 'SK', 'slovakya' => 'SK',
        'slovenia' => 'SI', 'slovenya' => 'SI',
        'sweden' => 'SE', 'isveç' => 'SE',
        'switzerland' => 'CH', 'İsviçre' => 'CH', 'isvicre' => 'CH',
        'albania' => 'AL', 'arnavutluk' => 'AL',
        'andorra' => 'AD',
        'armenia' => 'AM', 'ermenistan' => 'AM',
        'azerbaijan' => 'AZ', 'azerbaycan' => 'AZ',
        'belarus' => 'BY',
        'bosnia and herzegovina' => 'BA', 'bosna hersek' => 'BA',
        'georgia' => 'GE', 'gürcistan' => 'GE',
        'moldova' => 'MD',
        'monaco' => 'MC',
        'montenegro' => 'ME', 'karadağ' => 'ME',
        'north macedonia' => 'MK', 'kuzey makedonya' => 'MK',
        'serbia' => 'RS', 'sırbistan' => 'RS',
        'ukraine' => 'UA', 'ukrayna' => 'UA',
        'china' => 'CN', 'china mainland' => 'CN', 'çin' => 'CN',
        'hong kong' => 'HK',
        'macau' => 'MO', 'macao' => 'MO',
        'taiwan' => 'TW', 'tayvan' => 'TW',
        'south korea' => 'KR', 'korea' => 'KR', 'güney kore' => 'KR',
        'singapore' => 'SG', 'singapur' => 'SG',
        'malaysia' => 'MY', 'malezya' => 'MY',
        'thailand' => 'TH', 'tayland' => 'TH',
        'vietnam' => 'VN',
        'indonesia' => 'ID', 'endonezya' => 'ID',
        'philippines' => 'PH', 'filipinler' => 'PH',
        'india' => 'IN', 'hindistan' => 'IN',
        'australia' => 'AU', 'avustralya' => 'AU',
        'new zealand' => 'NZ', 'yeni zelanda' => 'NZ',
        'united arab emirates' => 'AE', 'uae' => 'AE', 'bae' => 'AE',
        'saudi arabia' => 'SA', 'suudi arabistan' => 'SA',
        'qatar' => 'QA', 'katar' => 'QA',
        'kuwait' => 'KW', 'kuveyt' => 'KW',
        'bahrain' => 'BH', 'bahreyn' => 'BH',
        'oman' => 'OM', 'umman' => 'OM',
        'israel' => 'IL', 'israil' => 'IL',
        'jordan' => 'JO', 'ürdün' => 'JO',
        'egypt' => 'EG', 'mısır' => 'EG',
        'morocco' => 'MA', 'fas' => 'MA',
        'south africa' => 'ZA', 'güney afrika' => 'ZA',
        'brazil' => 'BR', 'brezilya' => 'BR',
        'argentina' => 'AR', 'arjantin' => 'AR',
        'chile' => 'CL', 'şili' => 'CL',
        'colombia' => 'CO', 'kolombiya' => 'CO',
        'mexico' => 'MX', 'meksika' => 'MX',
        'canada' => 'CA', 'kanada' => 'CA',
        'europe' => 'EU', 'avrupa' => 'EU',
        'global' => 'GLOBAL',
    ];

    /**
     * Import all CSV files from directory into tgt_products table
     */
    public function importDirectory(?string $directoryPath = null): array
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        $dir = $directoryPath ?: base_path('fiyat_listesi');
        if (!is_dir($dir)) {
            return [
                'success' => false,
                'message' => "Klasör bulunamadı: {$dir}",
                'total_new' => 0,
                'total_updated' => 0,
            ];
        }

        $csvFiles = glob($dir . DIRECTORY_SEPARATOR . '*.csv');
        if (empty($csvFiles)) {
            return [
                'success' => false,
                'message' => "Klasörde hiç .csv dosyası bulunamadı.",
                'total_new' => 0,
                'total_updated' => 0,
            ];
        }

        $totalNew = 0;
        $totalUpdated = 0;
        $filesProcessed = 0;
        $seenCodesInRun = [];

        foreach ($csvFiles as $csvFile) {
            $filename = basename($csvFile);
            $res = $this->importSingleCsv($csvFile, $seenCodesInRun);
            $totalNew += $res['new'];
            $totalUpdated += $res['updated'];
            $filesProcessed++;
        }

        return [
            'success' => true,
            'message' => "Toplam {$filesProcessed} adet dosya başarıyla işlendi.",
            'files_processed' => $filesProcessed,
            'total_new' => $totalNew,
            'total_updated' => $totalUpdated,
            'unique_packages_in_batch' => count($seenCodesInRun),
            'total_in_db' => TgtProduct::count(),
        ];
    }

    /**
     * Process a single CSV file
     */
    public function importSingleCsv(string $filePath, array &$seenCodesInRun): array
    {
        $newCount = 0;
        $updatedCount = 0;

        if (!file_exists($filePath)) {
            return ['new' => 0, 'updated' => 0];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['new' => 0, 'updated' => 0];
        }

        $filename = basename($filePath);
        $cardType = $this->detectCardTypeFromFilename($filename);

        $line1 = fgetcsv($handle);
        $line2 = fgetcsv($handle);

        if (!$line1 || !$line2) {
            fclose($handle);
            return ['new' => 0, 'updated' => 0];
        }

        // Check if this is the Europe Matrix format (e.g. Europe Roaming / Hong Kong Roaming)
        $isMatrix = false;
        foreach ($line2 as $col) {
            if (stripos($col, '1GB/7 Days') !== false || stripos($col, '3GB/15 Days') !== false) {
                $isMatrix = true;
                break;
            }
        }

        if ($isMatrix) {
            // Read line 3 for matrix subheaders if present
            $line3 = fgetcsv($handle);
            $tiers = [
                ['name' => '1GB / 7 Gün', 'data' => 1, 'unit' => 'GB', 'days' => 7, 'price_col' => 2, 'code_col' => 17],
                ['name' => '3GB / 15 Gün', 'data' => 3, 'unit' => 'GB', 'days' => 15, 'price_col' => 3, 'code_col' => 18],
                ['name' => '5GB / 30 Gün', 'data' => 5, 'unit' => 'GB', 'days' => 30, 'price_col' => 4, 'code_col' => 19],
                ['name' => '7GB / 30 Gün', 'data' => 7, 'unit' => 'GB', 'days' => 30, 'price_col' => 5, 'code_col' => 20],
                ['name' => '10GB / 30 Gün', 'data' => 10, 'unit' => 'GB', 'days' => 30, 'price_col' => 6, 'code_col' => 21],
                ['name' => '20GB / 30 Gün', 'data' => 20, 'unit' => 'GB', 'days' => 30, 'price_col' => 7, 'code_col' => 22],
                ['name' => '30GB / 30 Gün', 'data' => 30, 'unit' => 'GB', 'days' => 30, 'price_col' => 8, 'code_col' => 23],
                ['name' => '40GB / 30 Gün', 'data' => 40, 'unit' => 'GB', 'days' => 30, 'price_col' => 9, 'code_col' => 24],
                ['name' => '50GB / 30 Gün', 'data' => 50, 'unit' => 'GB', 'days' => 30, 'price_col' => 10, 'code_col' => 25],
            ];

            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row[1])) continue;
                $countryName = trim($row[1]);
                $countryCodes = $this->resolveCountryCodes($countryName);
                $operator = trim($row[12] ?? ($row[11] ?? ''));

                foreach ($tiers as $tier) {
                    $priceVal = isset($row[$tier['price_col']]) ? (float)str_replace(',', '.', trim($row[$tier['price_col']])) : 0;
                    $productCode = isset($row[$tier['code_col']]) ? trim($row[$tier['code_col']]) : '';

                    if (empty($productCode) || $priceVal <= 0) {
                        continue;
                    }

                    $res = $this->saveOrUpdateProduct([
                        'product_code' => $productCode,
                        'product_name' => "{$countryName} {$tier['name']}",
                        'product_type' => 'DATA_PACK',
                        'country_code_list' => $countryCodes,
                        'mcc_list' => [],
                        'net_price' => $priceVal,
                        'data_total' => $tier['data'],
                        'data_unit' => $tier['unit'],
                        'usage_period' => $tier['days'],
                        'validity_period' => 60,
                        'card_type' => $cardType,
                        'raw_data' => [
                            'country' => $countryName,
                            'operator' => $operator,
                            'tier' => $tier['name'],
                            'source_file' => $filename,
                        ],
                    ], $seenCodesInRun);

                    if ($res === 'new') $newCount++;
                    elseif ($res === 'updated') $updatedCount++;
                }
            }
            fclose($handle);
            return ['new' => $newCount, 'updated' => $updatedCount];
        }

        // Standard CSV format
        // Find column indices
        $header = $line2;
        $colIdx = [
            'country' => $this->findColIndex($header, ['Country or region', 'Country', 'Region']),
            'type' => $this->findColIndex($header, ['Type']),
            'days' => $this->findColIndex($header, ['Days']),
            'package' => $this->findColIndex($header, ['Package']),
            'operator' => $this->findColIndex($header, ['Operator']),
            'price' => $this->findColIndex($header, ['Wholesale Price USD', 'Wholesale Price']),
            'code_auto' => $this->findColIndex($header, ['API Product code （Auto Activate）', 'API Product code (Auto Activate)']),
            'code_zd' => $this->findColIndex($header, ['API Product code （designated Date Activate）', 'API Product code']),
        ];

        // Defaults if headers not matched
        if ($colIdx['country'] === -1) $colIdx['country'] = 2;
        if ($colIdx['type'] === -1) $colIdx['type'] = 3;
        if ($colIdx['days'] === -1) $colIdx['days'] = 4;
        if ($colIdx['package'] === -1) $colIdx['package'] = 5;
        if ($colIdx['operator'] === -1) $colIdx['operator'] = 6;
        if ($colIdx['price'] === -1) $colIdx['price'] = 7;
        if ($colIdx['code_auto'] === -1) $colIdx['code_auto'] = 15;
        if ($colIdx['code_zd'] === -1) $colIdx['code_zd'] = 16;

        while (($row = fgetcsv($handle)) !== false) {
            $countryName = isset($row[$colIdx['country']]) ? trim($row[$colIdx['country']]) : '';
            if (empty($countryName) || is_numeric($countryName)) {
                // If country is empty or numeric, try checking col 1 or 2
                $countryName = trim($row[2] ?? ($row[1] ?? ''));
            }
            if (empty($countryName)) continue;

            $productCode = '';
            if (!empty($row[$colIdx['code_auto']])) {
                $productCode = trim($row[$colIdx['code_auto']]);
            } elseif (!empty($row[$colIdx['code_zd']])) {
                $productCode = trim($row[$colIdx['code_zd']]);
            }

            if (empty($productCode) || strlen($productCode) < 5) {
                // Check if any other column has the code (A-... or E-... or B-...)
                foreach ($row as $val) {
                    $v = trim($val);
                    if (preg_match('/^[A-Z]-[0-9]{3}-/i', $v)) {
                        $productCode = $v;
                        break;
                    }
                }
            }

            if (empty($productCode)) {
                continue;
            }

            $priceStr = isset($row[$colIdx['price']]) ? trim($row[$colIdx['price']]) : '0';
            $netPrice = (float)str_replace(['$', ' ', ','], ['', '', '.'], $priceStr);

            $packageText = isset($row[$colIdx['package']]) ? trim($row[$colIdx['package']]) : '';
            $daysVal = isset($row[$colIdx['days']]) ? (int)trim($row[$colIdx['days']]) : 1;
            if ($daysVal <= 0) $daysVal = 1;

            $typeText = isset($row[$colIdx['type']]) ? trim($row[$colIdx['type']]) : '';
            $isDaily = (stripos($typeText, 'Daypass') !== false || stripos($packageText, 'per day') !== false || stripos($productCode, '-D-') !== false);
            $productType = $isDaily ? 'DAILY_PACK' : 'DATA_PACK';

            // Parse data total and unit
            $dataTotal = 1;
            $dataUnit = 'GB';
            if (preg_match('/(\d+(?:\.\d+)?)\s*(GB|G|MB|M)\b/i', $packageText, $m)) {
                $dataTotal = (float)$m[1];
                $dataUnit = (stripos($m[2], 'M') !== false) ? 'MB' : 'GB';
            } elseif (preg_match('/-(\d+(?:\.\d+)?)(GB|MB|G|M)$/i', $productCode, $m)) {
                $dataTotal = (float)$m[1];
                $dataUnit = (stripos($m[2], 'M') !== false) ? 'MB' : 'GB';
            } elseif (stripos($packageText, 'Unlimited') !== false) {
                $dataTotal = 999;
                $dataUnit = 'GB';
            }

            // Parse validity period from product code e.g. /60D or /365D
            $validityPeriod = 30;
            if (preg_match('/\/(\d+)D/i', $productCode, $vm)) {
                $validityPeriod = (int)$vm[1];
            }

            $operator = isset($row[$colIdx['operator']]) ? trim($row[$colIdx['operator']]) : '';

            $productName = $this->buildCleanProductName($countryName, $packageText, $daysVal, $isDaily, $dataTotal, $dataUnit);

            $res = $this->saveOrUpdateProduct([
                'product_code' => $productCode,
                'product_name' => $productName,
                'product_type' => $productType,
                'country_code_list' => $this->resolveCountryCodes($countryName),
                'mcc_list' => [],
                'net_price' => $netPrice,
                'data_total' => $dataTotal,
                'data_unit' => $dataUnit,
                'usage_period' => $daysVal,
                'validity_period' => $validityPeriod,
                'card_type' => $cardType,
                'raw_data' => [
                    'country' => $countryName,
                    'package' => $packageText,
                    'operator' => $operator,
                    'type' => $typeText,
                    'days' => $daysVal,
                    'source_file' => $filename,
                ],
            ], $seenCodesInRun);

            if ($res === 'new') $newCount++;
            elseif ($res === 'updated') $updatedCount++;
        }

        fclose($handle);
        return ['new' => $newCount, 'updated' => $updatedCount];
    }

    /**
     * Update or create product safely
     */
    protected function saveOrUpdateProduct(array $data, array &$seenCodesInRun): string
    {
        $code = $data['product_code'];

        // If seen in current import run with same code, skip duplicate file overwrites if price unchanged
        if (isset($seenCodesInRun[$code])) {
            return 'skipped';
        }
        $seenCodesInRun[$code] = true;

        $existing = TgtProduct::where('product_code', $code)->first();

        if ($existing) {
            $existing->update([
                'net_price' => $data['net_price'],
                'card_type' => $data['card_type'] ?: $existing->card_type,
                'raw_data' => array_merge($existing->raw_data ?: [], $data['raw_data'] ?: []),
            ]);
            return 'updated';
        }

        TgtProduct::create($data);
        return 'new';
    }

    /**
     * Build clean readable product name
     */
    protected function buildCleanProductName(string $country, string $package, int $days, bool $isDaily, float $dataTotal, string $dataUnit): string
    {
        $quota = "{$dataTotal}{$dataUnit}";
        if ($dataTotal == 999 && $dataUnit == 'GB') {
            $quota = "Sınırsız";
        }

        if ($isDaily) {
            return "{$country} Günlük {$quota} ({$days} Gün)";
        }

        return "{$country} {$quota} ({$days} Gün)";
    }

    /**
     * Detect card type from filename
     */
    protected function detectCardTypeFromFilename(string $filename): string
    {
        if (preg_match('/__(ac2|ai1|ak1|CTExcel|ea1|eO1|eo2|eP1|eP2|eP3|C2|C4|I2|us1)/i', $filename, $m)) {
            return $m[1];
        }
        return 'eP1';
    }

    /**
     * Helper to find column index in CSV header
     */
    protected function findColIndex(array $header, array $candidates): int
    {
        foreach ($candidates as $cand) {
            foreach ($header as $idx => $colName) {
                if (stripos(trim((string)$colName), $cand) !== false) {
                    return $idx;
                }
            }
        }
        return -1;
    }

    /**
     * Map country name(s) to ISO 2-letter country code array
     */
    public function resolveCountryCodes(string $countryStr): array
    {
        $countryStr = str_replace(['&', '/', ',', '+'], '|', $countryStr);
        $parts = explode('|', $countryStr);
        $codes = [];

        foreach ($parts as $p) {
            $cleaned = strtolower(trim(preg_replace('/\(.*?\)/', '', $p)));
            if (empty($cleaned)) continue;

            if (isset($this->countryMap[$cleaned])) {
                $codes[] = $this->countryMap[$cleaned];
            } else {
                // If it's already a 2-letter uppercase code
                if (strlen($cleaned) === 2 && ctype_alpha($cleaned)) {
                    $codes[] = strtoupper($cleaned);
                } else {
                    $codes[] = strtoupper(substr($cleaned, 0, 2));
                }
            }
        }

        return array_values(array_unique($codes)) ?: ['GLOBAL'];
    }
}

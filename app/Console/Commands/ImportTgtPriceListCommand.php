<?php

namespace App\Console\Commands;

use App\Services\TgtPriceListImportService;
use Illuminate\Console\Command;

class ImportTgtPriceListCommand extends Command
{
    protected $signature = 'tgt:import-prices {--path= : Özel klasör yolu (varsayılan: fiyat_listesi)}';
    protected $description = 'Fiyat listesi klasöründeki tüm CSV dosyalarını okuyup TGT paketlerini veritabanına aktarır.';

    public function handle(TgtPriceListImportService $importService): int
    {
        $customPath = $this->option('path');
        $dir = $customPath ?: base_path('fiyat_listesi');

        $this->info("🚀 TGT Fiyat Listesi yükleme işlemi başlatılıyor...");
        $this->info("📂 Taranan Klasör: {$dir}");

        $result = $importService->importDirectory($dir);

        if (!$result['success']) {
            $this->error("❌ Hata: " . $result['message']);
            return Command::FAILURE;
        }

        $this->info("✅ İşlem Başarıyla Tamamlandı!");
        $this->table(
            ['Metrik', 'Değer'],
            [
                ['İşlenen CSV Dosya Sayısı', $result['files_processed']],
                ['Tekil Paket Sayısı (Dosyalarda)', $result['unique_packages_in_batch']],
                ['Sıfırdan Eklenen Yeni Paketler', $result['total_new']],
                ['Fiyatı Güncellenen Mevcut Paketler', $result['total_updated']],
                ['Veritabanındaki Toplam Aktif Paket', $result['total_in_db']],
            ]
        );

        return Command::SUCCESS;
    }
}

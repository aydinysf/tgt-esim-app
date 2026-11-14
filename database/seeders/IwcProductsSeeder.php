<?php

namespace Database\Seeders;

use App\Models\IwcProduct;
use Illuminate\Database\Seeder;

class IwcProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'id' => 58,
                'type' => 'ticket',
                'name' => '360 Istanbul Restaurant & Fine Dining',
                'slug' => '360-istanbul-restaurant',
                'description' => '<p>Tarihi Mısır Apartmanı terasında muhteşem 360 derece İstanbul manzarası eşliğinde gurme akşam yemeği ve eğlence deneyimi.</p><ul><li>Panoramik İstanbul ve Boğaz Manzarası</li><li>Ödüllü Şeflerden Türk ve Dünya Mutfağı</li><li>Akşam Yemeği & Gece Kulübü Atmosferi</li></ul>',
                'short_description' => 'Panoramik İstanbul manzarası eşliğinde gurme akşam yemeği ve şarap seçkisi.',
                'price' => 85.00,
                'regular_price' => 85.00,
                'currency' => 'EUR',
                'status' => 'Available',
                'availability_type' => 'time_point',
                'variants_data' => [
                    [
                        'id' => 115,
                        'label' => ['tr' => 'Pomegranate Champagne Menüsü (Kişi Başı)', 'en' => 'Pomegranate Champagne Array of Sushi'],
                        'price' => ['amount' => 85, 'regular_amount' => 85, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                    [
                        'id' => 116,
                        'label' => ['tr' => 'VIP Masada Şef Tadım Menüsü', 'en' => 'VIP Chef Tasting Menu'],
                        'price' => ['amount' => 120, 'regular_amount' => 120, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                ],
                'contents_data' => [
                    [
                        'title' => ['tr' => 'Restoran Detayları', 'en' => 'Details'],
                        'text' => ['tr' => '<p><strong>Hizmet Saatleri:</strong> 19:00 - 02:00<br><strong>Kıyafet Kuralı:</strong> Smart Casual<br><strong>Konum:</strong> İstiklal Caddesi, Mısır Apartmanı No:163 Beyoğlu/İstanbul</p>', 'en' => '<p>Fine dining & dinner show</p>'],
                    ],
                    [
                        'title' => ['tr' => 'İptal Politikası', 'en' => 'Cancellation Policy'],
                        'text' => ['tr' => '<p>Etkinlik saatinden 48 saat öncesine kadar ücretsiz iptal edilebilir.</p>', 'en' => '<p>Free cancellation up to 48 hours before.</p>'],
                    ],
                ],
            ],
            [
                'id' => 101,
                'type' => 'cruise',
                'name' => 'İstanbul Boğazı Akşam Yemekli Tekne Turu & Türk Gecesi',
                'slug' => 'bosphorus-dinner-cruise',
                'description' => '<p>Işıklar altında parıldayan İstanbul Boğazı\'nda lüks tekne ile 3 saatlik akşam yemekli seyir. Canlı müzik, oryantal ve halk oyunları gösterileri dahildir.</p><ul><li>3 Çeşit Akşam Yemeği & Sınırsız İçecek</li><li>Canlı Dans ve Müzik Gösterileri</li><li>Avrupa ve Asya Kıyıları Panoramik Seyri</li><li>Otelden Gidiş-Dönüş Transfer Opsiyonu</li></ul>',
                'short_description' => 'Boğazda sınırsız içecekli akşam yemeği ve canlı folklor gösterileri.',
                'price' => 45.00,
                'regular_price' => 55.00,
                'currency' => 'EUR',
                'status' => 'Available',
                'availability_type' => 'time_point',
                'variants_data' => [
                    [
                        'id' => 201,
                        'label' => ['tr' => 'Standart Menü (Alkolsüz İçecekler Dahil)', 'en' => 'Standard Menu with Soft Drinks'],
                        'price' => ['amount' => 45, 'regular_amount' => 55, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                    [
                        'id' => 202,
                        'label' => ['tr' => 'Premium Menü (Sınırsız Alkollü İçecekler)', 'en' => 'Premium Menu with Unlimited Alcoholic Drinks'],
                        'price' => ['amount' => 65, 'regular_amount' => 75, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                ],
                'contents_data' => [
                    [
                        'title' => ['tr' => 'Tur Programı', 'en' => 'Itinerary'],
                        'text' => ['tr' => '<p><strong>Tekne Kalkış:</strong> Kabataş İskelesi 20:00<br><strong>Dönüş:</strong> 23:30<br>Boğaz Köprüleri, Dolmabahçe Sarayı, Kız Kulesi manzarası eşliğinde seyahat.</p>', 'en' => '<p>3 hours cruise</p>'],
                    ],
                    [
                        'title' => ['tr' => 'İptal Politikası', 'en' => 'Cancellation Policy'],
                        'text' => ['tr' => '<p>Tur saatinden 24 saat öncesine kadar ücretsiz iptal edilebilir.</p>', 'en' => '<p>Free cancellation up to 24 hours.</p>'],
                    ],
                ],
            ],
            [
                'id' => 102,
                'type' => 'museum',
                'name' => 'Ayasofya & Sultanahmet Rehberli Deneyim Turu',
                'slug' => 'hagia-sophia-guided-tour',
                'description' => '<p>Uzman tarihçi rehber eşliğinde 1500 yıllık Ayasofya Camii ve Sultanahmet Meydanı\'nın gizli kalmış tarihi detaylarını keşfedin.</p><ul><li>Öncelikli Hızlı Giriş</li><li>Profesyonel Lisanslı İngilizce/Türkçe Rehber</li><li>Kulaklık Sistemi (Rehberi Net Duyabilme)</li></ul>',
                'short_description' => 'Lisanslı rehber eşliğinde Ayasofya ve tarihi yarımada yürüyüş turu.',
                'price' => 35.00,
                'regular_price' => 40.00,
                'currency' => 'EUR',
                'status' => 'Available',
                'availability_type' => 'time_point',
                'variants_data' => [
                    [
                        'id' => 203,
                        'label' => ['tr' => 'Yetişkin Bileti (12+ Yaş)', 'en' => 'Adult Ticket (12+)'],
                        'price' => ['amount' => 35, 'regular_amount' => 40, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                    [
                        'id' => 204,
                        'label' => ['tr' => 'Çocuk Bileti (4-11 Yaş)', 'en' => 'Child Ticket (4-11)'],
                        'price' => ['amount' => 20, 'regular_amount' => 25, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                ],
                'contents_data' => [
                    [
                        'title' => ['tr' => 'Buluşma Noktası', 'en' => 'Meeting Point'],
                        'text' => ['tr' => '<p>Sultanahmet Meydanı, Alman Çeşmesi önünde IWC rehberi sizi karşılar.</p>', 'en' => '<p>German Fountain</p>'],
                    ],
                ],
            ],
            [
                'id' => 103,
                'type' => 'museum',
                'name' => 'Topkapı Sarayı & Harem Dairesi Öncelikli Giriş Turu',
                'slug' => 'topkapi-palace-harem-tour',
                'description' => '<p>Osmanlı padişahlarının 400 yıllık ikametgahı Topkapı Sarayı ve gizemli Harem Dairesi\'ni sıra beklemeden keşfedin.</p><ul><li>Hızlı Giriş Bileti (Sıra Beklemeden)</li><li>Harem Dairesi Girişi Dahil</li><li>Sesli Rehber (Audio Guide) 10 Dilde</li></ul>',
                'short_description' => 'Sıra beklemeden Topkapı Sarayı ve Harem dairesi hızlı giriş paketi.',
                'price' => 42.00,
                'regular_price' => 48.00,
                'currency' => 'EUR',
                'status' => 'Available',
                'availability_type' => 'time_point',
                'variants_data' => [
                    [
                        'id' => 205,
                        'label' => ['tr' => 'Saray + Harem Tam Giriş Paketi', 'en' => 'Full Palace + Harem Pass'],
                        'price' => ['amount' => 42, 'regular_amount' => 48, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                ],
                'contents_data' => [
                    [
                        'title' => ['tr' => 'Ziyaret Bilgileri', 'en' => 'Visit Information'],
                        'text' => ['tr' => '<p><strong>Ziyaret Saatleri:</strong> 09:00 - 17:30 (Salı günleri kapalıdır)<br>Girişte dijital voucher kodunuzu göstermeniz yeterlidir.</p>', 'en' => '<p>Audio guide included</p>'],
                    ],
                ],
            ],
            [
                'id' => 104,
                'type' => 'ticket',
                'name' => 'Yerebatan Sarnıcı Sıra Beklemeden Hızlı Giriş',
                'slug' => 'basilica-cistern-fast-track',
                'description' => '<p>Bizans döneminin büyüleyici yeraltı su deposu Yerebatan Sarnıcı\'nı Medusa başları ve mistik ışıklandırmalarıyla sıra beklemeden ziyaret edin.</p><ul><li>Hızlı Giriş (Kuyruk Atlatma)</li><li>Dijital QR Bilet</li><li>Sesli Rehber Uygulaması</li></ul>',
                'short_description' => 'Mistik yeraltı sarnıcı hızlı giriş ve dijital sesli rehber.',
                'price' => 28.00,
                'regular_price' => 32.00,
                'currency' => 'EUR',
                'status' => 'Available',
                'availability_type' => 'time_point',
                'variants_data' => [
                    [
                        'id' => 206,
                        'label' => ['tr' => 'Hızlı Giriş Bilet (Kişi Başı)', 'en' => 'Fast Track Entry (Per Person)'],
                        'price' => ['amount' => 28, 'regular_amount' => 32, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                ],
                'contents_data' => [
                    [
                        'title' => ['tr' => 'Önemli Notlar', 'en' => 'Important Notes'],
                        'text' => ['tr' => '<p>Sarnıç her gün 09:00 - 19:00 saatleri arasında açıktır. Biletiniz seçtiğiniz gün boyu geçerlidir.</p>', 'en' => '<p>Valid for the chosen day</p>'],
                    ],
                ],
            ],
            [
                'id' => 105,
                'type' => 'show',
                'name' => 'Tarihi Hodjapasha Sema Ayini & Derviş Gösterisi',
                'slug' => 'whirling-dervishes-ceremony',
                'description' => '<p>550 yıllık tarihi hamam binasında UNESCO Kültür Mirası semazen gösterisi ile tasavvuf müziğinin huzurlu ritmine kapılın.</p><ul><li>1 Saatlik Canlı Tasavvuf Müziği & Sema Töreni</li><li>Tarihi Mekanda Oturmalı Düzen</li><li>Program Kitapçığı ve İkramlar</li></ul>',
                'short_description' => 'UNESCO korumasındaki büyüleyici Mevlevi sema töreni deneyimi.',
                'price' => 30.00,
                'regular_price' => 35.00,
                'currency' => 'EUR',
                'status' => 'Available',
                'availability_type' => 'time_point',
                'variants_data' => [
                    [
                        'id' => 207,
                        'label' => ['tr' => 'Standart Giriş (Numaralı Koltuk)', 'en' => 'Standard Seating'],
                        'price' => ['amount' => 30, 'regular_amount' => 35, 'currency' => 'EUR'],
                        'validity' => ['unit' => 'days', 'duration' => 1],
                    ],
                ],
                'contents_data' => [
                    [
                        'title' => ['tr' => 'Gösteri Saati', 'en' => 'Show Times'],
                        'text' => ['tr' => '<p>Her akşam 19:00. Lütfen gösteriden en az 15 dakika önce salonda bulununuz.</p>', 'en' => '<p>Every evening at 19:00</p>'],
                    ],
                ],
            ],
        ];

        foreach ($products as $p) {
            IwcProduct::updateOrCreate(
                ['id' => $p['id']],
                [
                    'type' => $p['type'],
                    'name' => $p['name'],
                    'slug' => $p['slug'],
                    'description' => $p['description'],
                    'short_description' => $p['short_description'],
                    'price' => $p['price'],
                    'regular_price' => $p['regular_price'],
                    'currency' => $p['currency'],
                    'status' => $p['status'],
                    'availability_type' => $p['availability_type'],
                    'variants_data' => $p['variants_data'],
                    'contents_data' => $p['contents_data'],
                    'raw_data' => $p,
                    'last_synced_at' => now(),
                ]
            );
        }
    }
}

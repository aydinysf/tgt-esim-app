<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TgtProduct;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin Accounts
        $admin = User::firstOrCreate(
            ['email' => 'admin@tgt.com'],
            [
                'name' => 'POLO SIM Sistem Yöneticisi',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'company_name' => 'POLO SIM HQ',
                'phone' => '+90 555 000 0000',
            ]
        );

        $admin2 = User::firstOrCreate(
            ['email' => 'admin@polosim.com'],
            [
                'name' => 'POLO SIM Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'company_name' => 'POLO SIM Global',
                'phone' => '+90 555 111 2222',
            ]
        );

        $admin3 = User::firstOrCreate(
            ['email' => 'aydinysf@polosim.com'],
            [
                'name' => 'Yusuf Aydın',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'company_name' => 'POLO SIM',
                'phone' => '+90 555 333 4444',
            ]
        );

        // 2. Create Customer Accounts
        $customer1 = User::firstOrCreate(
            ['email' => 'customer@tgt.com'],
            [
                'name' => 'Ahmet Yılmaz',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'company_name' => 'Yılmaz Turizm & Seyehat A.Ş.',
                'phone' => '+90 532 111 2233',
            ]
        );

        $customerPolo = User::firstOrCreate(
            ['email' => 'customer@polosim.com'],
            [
                'name' => 'Mehmet Kaya',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'company_name' => 'Kaya Telecom',
                'phone' => '+90 533 444 5566',
            ]
        );

        // 3. Seed TGT Products
        $p1 = TgtProduct::firstOrCreate(
            ['product_code' => 'A-002-ES-AU-T-30D/180D-3GB(A)'],
            [
                'product_name' => '【eSIM】Europe & USA 3GB / 30 Days',
                'product_type' => 'DATA_PACK',
                'country_code_list' => ['US', 'DE', 'FR', 'GB', 'IT', 'ES'],
                'mcc_list' => ['310', '262', '208'],
                'net_price' => 4.50,
                'data_total' => 3,
                'data_unit' => 'GB',
                'usage_period' => 30,
                'validity_period' => 180,
                'card_type' => 'M1',
            ]
        );

        $p2 = TgtProduct::firstOrCreate(
            ['product_code' => 'A-136-ES-AU-C4-1D/60D-1GB'],
            [
                'product_name' => '【eSIM】Asia 5 Countries 1GB Daily',
                'product_type' => 'DAILY_PACK',
                'country_code_list' => ['TR', 'JP', 'KR', 'SG', 'MY'],
                'mcc_list' => ['286', '440', '450'],
                'net_price' => 2.20,
                'data_total' => 1,
                'data_unit' => 'GB',
                'usage_period' => 1,
                'validity_period' => 60,
                'card_type' => 'C4',
            ]
        );

        $p3 = TgtProduct::firstOrCreate(
            ['product_code' => 'E-184-ES-AU-eO1-D-10D/60D-10GB'],
            [
                'product_name' => '【eSIM】Global 10GB / 10 Days High-Speed',
                'product_type' => 'DATA_PACK',
                'country_code_list' => ['TR', 'US', 'GB', 'FR', 'DE', 'AE', 'JP'],
                'mcc_list' => ['286', '310', '234'],
                'net_price' => 12.00,
                'data_total' => 10,
                'data_unit' => 'GB',
                'usage_period' => 10,
                'validity_period' => 60,
                'card_type' => 'Euro-eO1',
            ]
        );

        // 4. Assign Packages to Müşteri 1 (Ahmet Yılmaz) with custom prices
        CustomerPackage::firstOrCreate(
            ['user_id' => $customer1->id, 'tgt_product_id' => $p1->id],
            ['sale_price' => 9.90, 'is_active' => true] // net: 4.50 -> profit: 5.40
        );

        CustomerPackage::firstOrCreate(
            ['user_id' => $customer1->id, 'tgt_product_id' => $p2->id],
            ['sale_price' => 4.50, 'is_active' => true] // net: 2.20 -> profit: 2.30
        );

        CustomerPackage::firstOrCreate(
            ['user_id' => $customer1->id, 'tgt_product_id' => $p3->id],
            ['sale_price' => 22.00, 'is_active' => true] // net: 12.00 -> profit: 10.00
        );

        // Assign Packages to Müşteri 2 (Mehmet Kaya)
        CustomerPackage::firstOrCreate(
            ['user_id' => $customer2->id, 'tgt_product_id' => $p1->id],
            ['sale_price' => 8.50, 'is_active' => true] // net: 4.50 -> profit: 4.00
        );

        // 5. Seed Sample Orders
        $order1 = Order::firstOrCreate(
            ['channel_order_no' => 'TGT-ORD-20250801-001'],
            [
                'order_no' => 'TG202508011001',
                'user_id' => $customer1->id,
                'tgt_product_id' => $p1->id,
                'net_price' => 4.50,
                'sale_price' => 9.90,
                'profit' => 5.40,
                'iccid' => '89852342714026530001',
                'qr_code' => 'LPA:1$esiminfra.toprsp.com$98F57097621E451F8649135AC0A03011',
                'order_status' => 'ACTIVATED',
                'profile_status' => 'activated',
                'idempotency_key' => (string) Str::uuid(),
            ]
        );

        $order2 = Order::firstOrCreate(
            ['channel_order_no' => 'TGT-ORD-20250802-002'],
            [
                'order_no' => 'TG202508021002',
                'user_id' => $customer1->id,
                'tgt_product_id' => $p3->id,
                'net_price' => 12.00,
                'sale_price' => 22.00,
                'profit' => 10.00,
                'iccid' => '89852342714026530002',
                'qr_code' => 'LPA:1$esiminfra.toprsp.com$D0C23496B79C4179A7FA57E757C12299',
                'order_status' => 'INUSE',
                'profile_status' => 'downloaded',
                'idempotency_key' => (string) Str::uuid(),
            ]
        );

        // 6. Settings default
        Setting::set('tgt_environment', 'sandbox');
        Setting::set('tgt_base_url', 'https://enterpriseapisandbox.tugegroup.com:8070/openapi');
        Setting::set('tgt_account_id', 'TGT_Channel_Demo');
        Setting::set('tgt_secret', 'jzXUuQVIlFwf3peM');
    }
}

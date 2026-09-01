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

        // 2. Settings default (Official TGT Production credentials)
        Setting::set('tgt_environment', 'production');
        Setting::set('tgt_base_url', 'https://enterpriseapi.tugegroup.com:8070/openapi');
        Setting::set('tgt_account_id', 'checkfortrips');
        Setting::set('tgt_secret', 'RA0RQB54QNTRMWT9');
    }
}

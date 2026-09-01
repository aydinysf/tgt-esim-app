<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Purge all legacy customer package assignments and product catalog
        DB::table('customer_packages')->delete();
        DB::table('tgt_products')->delete();
    }

    public function down(): void
    {
        // No rollback needed
    }
};

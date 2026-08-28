<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tgt_products', function (Blueprint $table) {
            $table->index('product_name');
            $table->index('product_type');
            $table->index('usage_period');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('tgt_products', function (Blueprint $table) {
            $table->dropIndex(['product_name']);
            $table->dropIndex(['product_type']);
            $table->dropIndex(['usage_period']);
            $table->dropIndex(['created_at']);
        });
    }
};

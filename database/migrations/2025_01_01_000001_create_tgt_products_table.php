<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tgt_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->string('product_type')->default('DATA_PACK'); // DAILY_PACK, DATA_PACK
            $table->json('country_code_list')->nullable();
            $table->json('mcc_list')->nullable();
            $table->decimal('net_price', 10, 2)->default(0.00); // TGT Wholesale price
            $table->integer('data_total')->default(0);
            $table->string('data_unit')->default('GB'); // GB, MB
            $table->integer('usage_period')->default(0); // Days
            $table->integer('validity_period')->default(0); // Validity Days
            $table->string('card_type')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tgt_products');
    }
};

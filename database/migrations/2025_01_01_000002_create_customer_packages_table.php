<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tgt_product_id')->constrained('tgt_products')->onDelete('cascade');
            $table->decimal('sale_price', 10, 2); // Customer selling price set by Admin
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'tgt_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_packages');
    }
};

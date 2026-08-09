<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->nullable()->index(); // TGT Order No
            $table->string('channel_order_no')->unique(); // Our internal reference
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tgt_product_id')->constrained('tgt_products')->onDelete('cascade');
            $table->decimal('net_price', 10, 2); // TGT Wholesale cost
            $table->decimal('sale_price', 10, 2); // Sale price charged to customer
            $table->decimal('profit', 10, 2); // Calculated profit (sale_price - net_price)
            $table->string('iccid')->nullable()->index();
            $table->text('qr_code')->nullable();
            $table->string('order_status')->default('PENDING'); // PENDING, ACTIVATED, INUSE, USED, EXPIRED, CANCELLED
            $table->string('profile_status')->default('ungenerated'); // nodownload, activated, deleted, etc.
            $table->string('idempotency_key')->unique();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

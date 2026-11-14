<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iwc_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique(); // Our unique reference e.g. IWC-20261113-XXXXXX
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            
            // Product info
            $table->unsignedBigInteger('iwc_product_id');
            $table->string('product_name');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('variant_label')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('service_date'); // e.g. "2026-06-05 19:00"

            // Customer details
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('hotel_name')->nullable();
            $table->json('required_data')->nullable();

            // Pricing & Payment
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 10)->default('EUR');
            $table->string('payment_method')->default('stripe');
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
            $table->string('stripe_session_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable();

            // IWC API response details
            $table->unsignedBigInteger('iwc_order_id')->nullable()->index();
            $table->string('iwc_order_status')->nullable(); // pending_review, processing, confirmed, cancelled
            $table->json('iwc_response_data')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iwc_orders');
    }
};

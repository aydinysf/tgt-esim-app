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
        Schema::create('iwc_products', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Matches IWC product ID
            $table->string('type')->default('ticket');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('regular_price', 10, 2)->default(0);
            $table->string('currency', 10)->default('EUR');
            $table->string('status')->default('Available');
            $table->string('availability_type')->default('time_point');
            $table->json('variants_data')->nullable();
            $table->json('contents_data')->nullable();
            $table->json('required_order_data')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iwc_products');
    }
};

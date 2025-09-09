<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laraditz\Shopee\Models\ShopeeProduct;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopee_product_models', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignIdFor(ShopeeProduct::class, 'product_id')->nullable();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->json('price_info')->nullable();
            $table->json('stock_info')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('weight', 10)->nullable();
            $table->json('dimension')->nullable();
            $table->timestamps();

            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_product_models');
    }
};

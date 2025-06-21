<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopee_products', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('shop_id');
            $table->string('status', 50)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->boolean('has_model')->nullable();
            $table->timestamps();

            $table->index(['sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_products');
    }
};

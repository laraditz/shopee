<?php

use Illuminate\Support\Facades\Schema;
use Laraditz\Shopee\Models\ShopeeShop;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopee_webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(ShopeeShop::class, 'shop_id')->nullable();
            $table->unsignedInteger('code')->nullable();
            $table->json('data')->nullable();
            $table->unsignedInteger('sent_timestamp')->nullable();
            $table->timestamps();

            $table->index('shop_id');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_webhooks');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laraditz\Shopee\Models\ShopeeShop;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shopee_requests', function (Blueprint $table) {
            $table->foreignIdFor(ShopeeShop::class, 'shop_id')->nullable()->after('id');

            $table->index('shop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopee_requests', function (Blueprint $table) {
            //
        });
    }
};

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
        Schema::create('shopee_returns', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('shop_id')->nullable()->index();
            $table->string('order_sn')->nullable()->index();
            $table->string('status', 50)->nullable();
            $table->string('negotiation_status', 50)->nullable();
            $table->string('seller_proof_status', 50)->nullable();
            $table->string('seller_compensation_status', 50)->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->timestamp('return_created_at')->nullable();
            $table->timestamp('return_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_returns');
    }
};

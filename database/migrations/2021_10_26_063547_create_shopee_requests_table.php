<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action', 100)->nullable();
            $table->string('url')->nullable();
            $table->string('request_id', 50)->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->string('error', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopee_requests');
    }
}

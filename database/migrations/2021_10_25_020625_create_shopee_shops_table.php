<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_shops', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name')->nullable();
            $table->string('region', 50)->nullable();
            $table->smallInteger('status')->nullable();
            $table->string('code')->nullable();
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
        Schema::dropIfExists('shopee_shops');
    }
}

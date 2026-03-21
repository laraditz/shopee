<?php

namespace Laraditz\Shopee\Tests\Unit;

use Laraditz\Shopee\Models\ShopeeProduct;
use Laraditz\Shopee\Models\ShopeeProductModel;
use Laraditz\Shopee\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class ProductServiceHooksTest extends TestCase
{
    /** @test */
    public function shopee_products_table_has_deleted_at_column()
    {
        $this->assertTrue(Schema::hasColumn('shopee_products', 'deleted_at'));
    }

    /** @test */
    public function shopee_product_models_table_has_deleted_at_column()
    {
        $this->assertTrue(Schema::hasColumn('shopee_product_models', 'deleted_at'));
    }
}

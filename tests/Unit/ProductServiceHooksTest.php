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

    /** @test */
    public function shopee_product_uses_soft_deletes()
    {
        $product = ShopeeProduct::create([
            'id' => '111',
            'shop_id' => 1,
            'status' => 'NORMAL',
        ]);

        $product->delete();

        $this->assertSoftDeleted('shopee_products', ['id' => '111']);
        $this->assertNull(ShopeeProduct::find('111'));
        $this->assertNotNull(ShopeeProduct::withTrashed()->find('111'));
    }

    /** @test */
    public function shopee_product_model_uses_soft_deletes()
    {
        $productModel = ShopeeProductModel::create([
            'id' => '999',
            'product_id' => null,
            'name' => 'Red',
        ]);

        $productModel->delete();

        $this->assertSoftDeleted('shopee_product_models', ['id' => '999']);
        $this->assertNull(ShopeeProductModel::find('999'));
        $this->assertNotNull(ShopeeProductModel::withTrashed()->find('999'));
    }
}

<?php

namespace Laraditz\Shopee\Tests\Unit;

use Laraditz\Shopee\Models\ShopeeProduct;
use Laraditz\Shopee\Models\ShopeeProductModel;
use Laraditz\Shopee\Models\ShopeeRequest;
use Laraditz\Shopee\Services\ProductService;
use Laraditz\Shopee\Shopee;
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

    /** @test */
    public function after_add_item_response_creates_product_stub()
    {
        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::addItem',
            'url'     => 'https://example.com',
        ]);

        $result = [
            'response' => ['item_id' => '42'],
        ];

        // Intercept the secondary getItemBaseInfo call — it will fail gracefully
        // because there's no real HTTP client; we only test the stub creation here.
        try {
            $service->afterAddItemResponse($request, $result);
        } catch (\Throwable $e) {
            // Expected: getItemBaseInfo HTTP call fails in unit test environment
        }

        $this->assertDatabaseHas('shopee_products', [
            'id'      => '42',
            'shop_id' => 1,
            'status'  => 'NORMAL',
        ]);
    }

    /** @test */
    public function after_add_item_response_does_nothing_when_item_id_missing()
    {
        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::addItem',
            'url'     => 'https://example.com',
        ]);

        $service->afterAddItemResponse($request, []);

        $this->assertDatabaseCount('shopee_products', 0);
    }
}

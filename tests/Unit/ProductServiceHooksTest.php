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

    /** @test */
    public function after_update_item_response_updates_existing_product_name()
    {
        ShopeeProduct::create([
            'id'      => '55',
            'shop_id' => 1,
            'status'  => 'NORMAL',
            'name'    => 'Old Name',
        ]);

        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::updateItem',
            'url'     => 'https://example.com',
            'request' => ['item_id' => '55', 'item_name' => 'New Name'],
        ]);

        $result = ['response' => ['item_id' => '55']];

        $service->afterUpdateItemResponse($request, $result);

        $this->assertDatabaseHas('shopee_products', [
            'id'   => '55',
            'name' => 'New Name',
        ]);
    }

    /** @test */
    public function after_update_item_response_does_not_overwrite_name_with_null()
    {
        ShopeeProduct::create([
            'id'      => '66',
            'shop_id' => 1,
            'status'  => 'NORMAL',
            'name'    => 'Keep This Name',
        ]);

        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::updateItem',
            'url'     => 'https://example.com',
            'request' => ['item_id' => '66'], // no item_name
        ]);

        $result = ['response' => ['item_id' => '66']];

        $service->afterUpdateItemResponse($request, $result);

        $this->assertDatabaseHas('shopee_products', [
            'id'   => '66',
            'name' => 'Keep This Name',
        ]);
    }

    /** @test */
    public function after_update_item_response_does_not_insert_when_product_not_found()
    {
        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::updateItem',
            'url'     => 'https://example.com',
            'request' => ['item_id' => '999', 'item_name' => 'Ghost'],
        ]);

        $result = ['response' => ['item_id' => '999']];

        $service->afterUpdateItemResponse($request, $result);

        $this->assertDatabaseCount('shopee_products', 0);
    }

    /** @test */
    public function after_update_item_response_falls_back_to_request_item_id()
    {
        ShopeeProduct::create([
            'id'      => '77',
            'shop_id' => 1,
            'status'  => 'NORMAL',
            'name'    => 'Before',
        ]);

        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::updateItem',
            'url'     => 'https://example.com',
            'request' => ['item_id' => '77', 'item_name' => 'After'],
        ]);

        // No item_id in response — should fall back to request payload
        $result = ['response' => []];

        $service->afterUpdateItemResponse($request, $result);

        $this->assertDatabaseHas('shopee_products', [
            'id'   => '77',
            'name' => 'After',
        ]);
    }

    /** @test */
    public function after_delete_item_response_soft_deletes_product_and_models()
    {
        ShopeeProduct::create([
            'id'      => '88',
            'shop_id' => 1,
            'status'  => 'NORMAL',
            'name'    => 'To Delete',
        ]);

        ShopeeProductModel::create(['id' => 'm1', 'product_id' => '88', 'name' => 'Model A']);
        ShopeeProductModel::create(['id' => 'm2', 'product_id' => '88', 'name' => 'Model B']);

        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::deleteItem',
            'url'     => 'https://example.com',
            'request' => ['item_id' => '88'],
        ]);

        $service->afterDeleteItemResponse($request, []);

        $this->assertSoftDeleted('shopee_products', ['id' => '88']);
        $this->assertNull(ShopeeProduct::find('88'));
        $this->assertSoftDeleted('shopee_product_models', ['id' => 'm1']);
        $this->assertSoftDeleted('shopee_product_models', ['id' => 'm2']);
        $this->assertCount(0, ShopeeProductModel::where('product_id', '88')->get());
    }

    /** @test */
    public function after_delete_item_response_does_nothing_when_product_not_found()
    {
        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::deleteItem',
            'url'     => 'https://example.com',
            'request' => ['item_id' => '999'],
        ]);

        $service->afterDeleteItemResponse($request, []);

        $this->assertDatabaseCount('shopee_products', 0);
    }

    /** @test */
    public function after_delete_item_response_does_nothing_when_item_id_missing_from_request()
    {
        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::deleteItem',
            'url'     => 'https://example.com',
            'request' => [],
        ]);

        $service->afterDeleteItemResponse($request, []);

        $this->assertDatabaseCount('shopee_products', 0);
    }

    /** @test */
    public function after_delete_item_response_is_scoped_to_shop_id()
    {
        ShopeeProduct::create(['id' => '101', 'shop_id' => 2, 'status' => 'NORMAL']);

        $shopee = new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1);
        $service = new ProductService($shopee);

        $request = ShopeeRequest::create([
            'shop_id' => 1,
            'action'  => 'ProductService::deleteItem',
            'url'     => 'https://example.com',
            'request' => ['item_id' => '101'],
        ]);

        $service->afterDeleteItemResponse($request, []);

        $this->assertNotNull(ShopeeProduct::find('101'));
    }
}

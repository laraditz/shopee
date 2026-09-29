<?php

namespace Laraditz\Shopee\Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Laraditz\Shopee\Models\ShopeeOrder;
use Laraditz\Shopee\Models\ShopeeReturn;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Tests\TestCase;

class ShopeeReturnTest extends TestCase
{
    /** @test */
    public function shopee_returns_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasColumns('shopee_returns', [
            'id',
            'shop_id',
            'order_sn',
            'status',
            'negotiation_status',
            'seller_proof_status',
            'seller_compensation_status',
            'refund_amount',
            'currency',
            'return_created_at',
            'return_updated_at',
            'created_at',
            'updated_at',
        ]));
    }

    /** @test */
    public function shopee_return_uses_string_primary_key_and_casts()
    {
        ShopeeReturn::create([
            'id' => '2209010001',
            'shop_id' => 1,
            'order_sn' => 'ORDER1',
            'refund_amount' => 12.5,
        ]);

        $return = ShopeeReturn::find('2209010001');

        $this->assertNotNull($return);
        $this->assertSame('2209010001', $return->id);
        $this->assertSame('12.50', $return->refund_amount);
        $this->assertSame(1, $return->shop_id);
    }

    /** @test */
    public function shopee_return_belongs_to_shop_and_order()
    {
        ShopeeShop::create(['id' => 1, 'name' => 'Shop A']);
        ShopeeOrder::create(['id' => 'ORDER1', 'shop_id' => 1]);
        $return = ShopeeReturn::create(['id' => 'R1', 'shop_id' => 1, 'order_sn' => 'ORDER1']);

        $this->assertSame(1, $return->shop->id);
        $this->assertSame('ORDER1', $return->order->id);
    }

    /** @test */
    public function shopee_return_order_is_null_when_order_not_synced()
    {
        $return = ShopeeReturn::create(['id' => 'R1', 'shop_id' => 1, 'order_sn' => 'UNKNOWN']);

        $this->assertNull($return->order);
    }

    /** @test */
    public function shop_and_order_have_many_returns()
    {
        $shop = ShopeeShop::create(['id' => 1, 'name' => 'Shop A']);
        $order = ShopeeOrder::create(['id' => 'ORDER1', 'shop_id' => 1]);
        ShopeeReturn::create(['id' => 'R1', 'shop_id' => 1, 'order_sn' => 'ORDER1']);
        ShopeeReturn::create(['id' => 'R2', 'shop_id' => 1, 'order_sn' => 'ORDER1']);
        ShopeeReturn::create(['id' => 'R3', 'shop_id' => 2, 'order_sn' => 'ORDER2']);

        $this->assertEqualsCanonicalizing(['R1', 'R2'], $shop->returns->pluck('id')->all());
        $this->assertEqualsCanonicalizing(['R1', 'R2'], $order->returns->pluck('id')->all());
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'return_sn' => 2209010001,
            'order_sn' => 'ORDER1',
            'status' => 'REQUESTED',
            'refund_amount' => 25.9,
            'currency' => 'MYR',
            'create_time' => 1700000000,
            'update_time' => 1700003600,
            'negotiation' => ['negotiation_status' => 'PENDING_RESPOND'],
            'seller_proof' => ['seller_proof_status' => 'PENDING'],
            'seller_compensation' => ['seller_compensation_status' => 'NOT_REQUIRED'],
            'user' => ['username' => 'buyer', 'email' => 'buyer@example.com'],
        ], $overrides);
    }

    /** @test */
    public function sync_from_payload_maps_all_fields()
    {
        $return = ShopeeReturn::syncFromPayload($this->payload(), 1);

        $this->assertInstanceOf(ShopeeReturn::class, $return);

        $fresh = ShopeeReturn::find('2209010001');
        $this->assertSame(1, $fresh->shop_id);
        $this->assertSame('ORDER1', $fresh->order_sn);
        $this->assertSame('REQUESTED', $fresh->status);
        $this->assertSame('PENDING_RESPOND', $fresh->negotiation_status);
        $this->assertSame('PENDING', $fresh->seller_proof_status);
        $this->assertSame('NOT_REQUIRED', $fresh->seller_compensation_status);
        $this->assertSame('25.90', $fresh->refund_amount);
        $this->assertSame('MYR', $fresh->currency);
        $this->assertSame(1700000000, $fresh->return_created_at->getTimestamp());
        $this->assertSame(1700003600, $fresh->return_updated_at->getTimestamp());
    }

    /** @test */
    public function sync_from_payload_updates_existing_row()
    {
        ShopeeReturn::syncFromPayload($this->payload(), 1);
        ShopeeReturn::syncFromPayload($this->payload(['status' => 'ACCEPTED']), 1);

        $this->assertDatabaseCount('shopee_returns', 1);
        $this->assertSame('ACCEPTED', ShopeeReturn::find('2209010001')->status);
    }

    /** @test */
    public function sync_from_payload_returns_null_without_return_sn()
    {
        $this->assertNull(ShopeeReturn::syncFromPayload(['order_sn' => 'ORDER1'], 1));
        $this->assertNull(ShopeeReturn::syncFromPayload(['return_sn' => ''], 1));
        $this->assertDatabaseCount('shopee_returns', 0);
    }

    /** @test */
    public function sync_from_payload_does_not_overwrite_with_missing_or_null_fields()
    {
        ShopeeReturn::syncFromPayload($this->payload(), 1);

        ShopeeReturn::syncFromPayload([
            'return_sn' => 2209010001,
            'status' => 'ACCEPTED',
            'refund_amount' => null,
        ], 1);

        $fresh = ShopeeReturn::find('2209010001');
        $this->assertSame('ACCEPTED', $fresh->status);
        $this->assertSame('ORDER1', $fresh->order_sn);
        $this->assertSame('25.90', $fresh->refund_amount);
        $this->assertSame('PENDING_RESPOND', $fresh->negotiation_status);
        $this->assertSame(1700003600, $fresh->return_updated_at->getTimestamp());
    }

    /** @test */
    public function sync_from_payload_falls_back_to_flat_status_keys()
    {
        ShopeeReturn::syncFromPayload($this->payload([
            'negotiation' => [],
            'seller_proof' => null,
            'seller_compensation' => null,
            'negotiation_status' => 'TERMINATED',
            'seller_proof_status' => 'UPLOADED',
            'seller_compensation_status' => 'PENDING_REQUEST',
        ]), 1);

        $fresh = ShopeeReturn::find('2209010001');
        $this->assertSame('TERMINATED', $fresh->negotiation_status);
        $this->assertSame('UPLOADED', $fresh->seller_proof_status);
        $this->assertSame('PENDING_REQUEST', $fresh->seller_compensation_status);
    }

    /** @test */
    public function sync_from_payload_skips_zero_or_invalid_timestamps()
    {
        ShopeeReturn::syncFromPayload($this->payload(['create_time' => 0, 'update_time' => 'abc']), 1);

        $fresh = ShopeeReturn::find('2209010001');
        $this->assertNull($fresh->return_created_at);
        $this->assertNull($fresh->return_updated_at);
    }

    /** @test */
    public function sync_from_payload_keeps_shop_id_when_null_given()
    {
        ShopeeReturn::syncFromPayload($this->payload(), 1);
        ShopeeReturn::syncFromPayload($this->payload(['status' => 'ACCEPTED']), null);

        $this->assertSame(1, ShopeeReturn::find('2209010001')->shop_id);
    }
}

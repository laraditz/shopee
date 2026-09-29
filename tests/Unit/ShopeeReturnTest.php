<?php

namespace Laraditz\Shopee\Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Laraditz\Shopee\Models\ShopeeReturn;
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
}

<?php

namespace Laraditz\Shopee\Tests\Unit;

use Laraditz\Shopee\Shopee;
use Laraditz\Shopee\Tests\TestCase;

class ShopeeTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_shopee_class()
    {
        $shopee = new Shopee();
        
        $this->assertInstanceOf(Shopee::class, $shopee);
    }

    /** @test */
    public function it_can_create_shopee_instance_with_make_method()
    {
        $shopee = Shopee::make();
        
        $this->assertInstanceOf(Shopee::class, $shopee);
    }

    /** @test */
    public function it_can_set_partner_credentials()
    {
        $shopee = new Shopee(
            partner_id: 'test_partner',
            partner_key: 'test_key',
            shop_id: 123456789
        );
        
        $this->assertEquals('test_partner', $shopee->getPartnerId());
        $this->assertEquals('test_key', $shopee->getPartnerKey());
        $this->assertEquals(123456789, $shopee->getShopId());
    }

    /** @test */
    public function it_can_access_services()
    {
        $shopee = new Shopee(
            partner_id: 'test_partner',
            partner_key: 'test_key'
        );
        
        $this->assertInstanceOf(\Laraditz\Shopee\Services\AuthService::class, $shopee->auth());
        $this->assertInstanceOf(\Laraditz\Shopee\Services\ShopService::class, $shopee->shop());
        $this->assertInstanceOf(\Laraditz\Shopee\Services\ProductService::class, $shopee->product());
        $this->assertInstanceOf(\Laraditz\Shopee\Services\OrderService::class, $shopee->order());
        $this->assertInstanceOf(\Laraditz\Shopee\Services\PaymentService::class, $shopee->payment());
    }

    /** @test */
    public function it_generates_correct_signature()
    {
        $shopee = new Shopee(
            partner_id: '123456',
            partner_key: 'test_key'
        );
        
        $signature = $shopee->generateSignature('/api/v2/test', ['param1', 'param2']);
        
        $this->assertIsArray($signature);
        $this->assertArrayHasKey('signature', $signature);
        $this->assertArrayHasKey('time', $signature);
        $this->assertIsString($signature['signature']);
        $this->assertIsInt($signature['time']);
    }
}
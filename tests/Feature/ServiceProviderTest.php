<?php

namespace Laraditz\Shopee\Tests\Feature;

use Laraditz\Shopee\Facades\Shopee;
use Laraditz\Shopee\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_shopee_service_in_container()
    {
        $shopee = app('shopee');
        
        $this->assertInstanceOf(\Laraditz\Shopee\Shopee::class, $shopee);
    }

    /** @test */
    public function it_can_use_shopee_facade()
    {
        $this->assertInstanceOf(\Laraditz\Shopee\Shopee::class, Shopee::getFacadeRoot());
    }

    /** @test */
    public function it_loads_configuration()
    {
        $this->assertEquals('test_partner_id', config('shopee.partner_id'));
        $this->assertEquals('test_partner_key', config('shopee.partner_key'));
        $this->assertEquals(12345678, config('shopee.shop_id'));
        $this->assertTrue(config('shopee.sandbox.mode'));
    }

    /** @test */
    public function it_registers_commands()
    {
        $commands = $this->app->make('Illuminate\Contracts\Console\Kernel')->all();
        
        $this->assertArrayHasKey('shopee:refresh-token', $commands);
        $this->assertArrayHasKey('shopee:flush-expired-token', $commands);
    }
}
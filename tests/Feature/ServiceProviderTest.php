<?php

namespace Laraditz\Shopee\Tests\Feature;

use Laraditz\Shopee\Facades\Shopee;
use Laraditz\Shopee\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_shopee_service_in_container()
    {
        $shopee = app('shopee');
        
        $this->assertInstanceOf(\Laraditz\Shopee\Shopee::class, $shopee);
    }

    #[Test]
    public function it_can_use_shopee_facade()
    {
        $this->assertInstanceOf(\Laraditz\Shopee\Shopee::class, Shopee::getFacadeRoot());
    }

    #[Test]
    public function it_loads_configuration()
    {
        $this->assertEquals('test_partner_id', config('shopee.partner_id'));
        $this->assertEquals('test_partner_key', config('shopee.partner_key'));
        $this->assertEquals(12345678, config('shopee.shop_id'));
        $this->assertTrue(config('shopee.sandbox.mode'));
    }

    #[Test]
    public function it_has_redirect_url_config_key()
    {
        $this->assertArrayHasKey('redirect_url', config('shopee'));
        $this->assertNull(config('shopee.redirect_url'));
    }

    #[Test]
    public function it_registers_commands()
    {
        $commands = $this->app->make('Illuminate\Contracts\Console\Kernel')->all();
        
        $this->assertArrayHasKey('shopee:refresh-token', $commands);
        $this->assertArrayHasKey('shopee:flush-expired-token', $commands);
    }
}
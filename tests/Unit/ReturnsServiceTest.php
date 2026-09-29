<?php

namespace Laraditz\Shopee\Tests\Unit;

use BadMethodCallException;
use Laraditz\Shopee\Services\ReturnsService;
use Laraditz\Shopee\Shopee;
use Laraditz\Shopee\Tests\TestCase;

class ReturnsServiceTest extends TestCase
{
    /** @test */
    public function returns_routes_are_configured()
    {
        $this->assertSame([
            'get_return_list' => '/api/v2/returns/get_return_list',
            'get_return_detail' => '/api/v2/returns/get_return_detail',
        ], config('shopee.routes.returns'));
    }

    /** @test */
    public function it_resolves_returns_service()
    {
        $this->assertInstanceOf(ReturnsService::class, Shopee::make()->returns());
    }

    /** @test */
    public function it_throws_for_unsupported_returns_method()
    {
        $this->expectException(BadMethodCallException::class);

        Shopee::make()->returns()->confirm(return_sn: 'R1');
    }
}

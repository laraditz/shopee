<?php

namespace Laraditz\Shopee\Tests\Unit;

use BadMethodCallException;
use Laraditz\Shopee\Models\ShopeeRequest;
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

    private function makeRequest(string $action): ShopeeRequest
    {
        return ShopeeRequest::create([
            'shop_id' => 1,
            'action' => 'ReturnsService::' . $action,
            'url' => 'https://example.com',
        ]);
    }

    private function service(): ReturnsService
    {
        return new ReturnsService(new Shopee(partner_id: 'pid', partner_key: 'pkey', shop_id: 1));
    }

    /** @test */
    public function after_get_return_list_response_syncs_each_return()
    {
        $this->service()->afterGetReturnListResponse($this->makeRequest('getReturnList'), [
            'response' => [
                'more' => false,
                'return' => [
                    ['return_sn' => 'R1', 'order_sn' => 'O1', 'status' => 'REQUESTED'],
                    'not-an-array',
                    ['return_sn' => 'R2', 'order_sn' => 'O2', 'status' => 'ACCEPTED'],
                ],
            ],
        ]);

        $this->assertDatabaseCount('shopee_returns', 2);
        $this->assertDatabaseHas('shopee_returns', ['id' => 'R1', 'shop_id' => 1, 'order_sn' => 'O1', 'status' => 'REQUESTED']);
        $this->assertDatabaseHas('shopee_returns', ['id' => 'R2', 'shop_id' => 1, 'order_sn' => 'O2', 'status' => 'ACCEPTED']);
    }

    /** @test */
    public function after_get_return_detail_response_syncs_the_return()
    {
        $this->service()->afterGetReturnDetailResponse($this->makeRequest('getReturnDetail'), [
            'response' => ['return_sn' => 'R1', 'order_sn' => 'O1', 'status' => 'REFUND_PAID'],
        ]);

        $this->assertDatabaseHas('shopee_returns', ['id' => 'R1', 'shop_id' => 1, 'status' => 'REFUND_PAID']);
    }

    /** @test */
    public function hooks_do_nothing_when_response_missing()
    {
        $service = $this->service();

        $service->afterGetReturnListResponse($this->makeRequest('getReturnList'), ['error' => 'error_param']);
        $service->afterGetReturnListResponse($this->makeRequest('getReturnList'), ['response' => ['return' => null]]);
        $service->afterGetReturnDetailResponse($this->makeRequest('getReturnDetail'), ['error' => 'error_param']);
        $service->afterGetReturnDetailResponse($this->makeRequest('getReturnDetail'), null);

        $this->assertDatabaseCount('shopee_returns', 0);
    }
}

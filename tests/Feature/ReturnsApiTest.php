<?php

namespace Laraditz\Shopee\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Shopee;
use Laraditz\Shopee\Tests\TestCase;

class ReturnsApiTest extends TestCase
{
    private const SHOP_ID = 12345678;

    protected function setUp(): void
    {
        parent::setUp();

        $shop = ShopeeShop::create(['id' => self::SHOP_ID, 'name' => 'Test Shop']);
        $shop->accessToken()->create([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_at' => now()->addHours(4),
        ]);
    }

    /** @test */
    public function get_return_list_calls_endpoint_and_syncs_returns()
    {
        $body = [
            'request_id' => 'req-list',
            'error' => '',
            'message' => '',
            'response' => [
                'more' => false,
                'return' => [
                    [
                        'return_sn' => 'R1',
                        'order_sn' => 'O1',
                        'status' => 'REQUESTED',
                        'refund_amount' => 10.5,
                        'currency' => 'MYR',
                        'update_time' => 1700003600,
                    ],
                ],
            ],
        ];

        Http::fake(['*/api/v2/returns/get_return_list*' => Http::response($body)]);

        $result = Shopee::make()->returns()->getReturnList(page_no: 0, page_size: 10);

        $this->assertSame($body, $result);

        Http::assertSent(function (Request $request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->method() === 'GET'
                && str_contains($request->url(), '/api/v2/returns/get_return_list?')
                && $query['page_no'] === '0'
                && $query['page_size'] === '10'
                && $query['partner_id'] === 'test_partner_id'
                && $query['shop_id'] === (string) self::SHOP_ID
                && $query['access_token'] === 'test_access_token'
                && !empty($query['sign'])
                && !empty($query['timestamp']);
        });

        $this->assertDatabaseHas('shopee_returns', [
            'id' => 'R1',
            'shop_id' => self::SHOP_ID,
            'order_sn' => 'O1',
            'status' => 'REQUESTED',
            'currency' => 'MYR',
        ]);
    }

    /** @test */
    public function get_return_detail_calls_endpoint_and_syncs_return()
    {
        $body = [
            'request_id' => 'req-detail',
            'error' => '',
            'message' => '',
            'response' => [
                'return_sn' => 'R1',
                'order_sn' => 'O1',
                'status' => 'ACCEPTED',
                'negotiation' => ['negotiation_status' => 'TERMINATED'],
            ],
        ];

        Http::fake(['*/api/v2/returns/get_return_detail*' => Http::response($body)]);

        $result = Shopee::make()->returns()->getReturnDetail(return_sn: 'R1');

        $this->assertSame($body, $result);

        Http::assertSent(function (Request $request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->method() === 'GET'
                && str_contains($request->url(), '/api/v2/returns/get_return_detail?')
                && $query['return_sn'] === 'R1';
        });

        $this->assertDatabaseHas('shopee_returns', [
            'id' => 'R1',
            'shop_id' => self::SHOP_ID,
            'status' => 'ACCEPTED',
            'negotiation_status' => 'TERMINATED',
        ]);
    }

    /** @test */
    public function shopee_error_body_is_returned_without_syncing()
    {
        $body = [
            'request_id' => 'req-error',
            'error' => 'error_param',
            'message' => 'Invalid return_sn',
        ];

        Http::fake(['*/api/v2/returns/get_return_detail*' => Http::response($body)]);

        $result = Shopee::make()->returns()->getReturnDetail(return_sn: 'BAD');

        $this->assertSame($body, $result);
        $this->assertDatabaseCount('shopee_returns', 0);
        $this->assertDatabaseHas('shopee_requests', [
            'action' => 'ReturnsService::getReturnDetail',
            'error' => 'error_param',
        ]);
    }
}

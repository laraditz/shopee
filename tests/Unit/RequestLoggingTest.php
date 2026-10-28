<?php

namespace Laraditz\Shopee\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laraditz\Shopee\Models\ShopeeRequest;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Shopee;
use Laraditz\Shopee\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RequestLoggingTest extends TestCase
{
    private function exchange(): ?object
    {
        Http::fake(['*' => Http::response([
            'access_token' => 'real-at',
            'refresh_token' => 'real-rt',
            'expire_in' => 14400,
        ])]);

        ShopeeShop::create(['id' => 12345678, 'code' => 'real-code']);

        return (new Shopee(partner_id: 'test_partner_id', partner_key: 'test_partner_key'))
            ->auth()->accessToken(12345678);
    }

    private function row(): object
    {
        return DB::table('shopee_requests')->first();
    }

    #[Test]
    public function test_default_logs_unredacted_request()
    {
        $this->exchange();

        $this->assertSame(1, DB::table('shopee_requests')->count());
        $this->assertSame('real-code', json_decode($this->row()->request, true)['code']);
        $this->assertSame('real-at', json_decode($this->row()->response, true)['access_token']);
        $this->assertStringContainsString('sign=', $this->row()->url);
        $this->assertStringNotContainsString('REDACTED', $this->row()->url);
    }

    #[Test]
    public function test_missing_logging_config_behaves_as_default()
    {
        config()->set('shopee.logging', null);
        $this->exchange();

        $this->assertSame(1, DB::table('shopee_requests')->count());
        $this->assertSame('real-code', json_decode($this->row()->request, true)['code']);
        $this->assertSame('real-at', json_decode($this->row()->response, true)['access_token']);
    }

    #[Test]
    public function test_logging_disabled_writes_no_row_but_access_token_is_stored()
    {
        config()->set('shopee.logging.requests', false);
        $token = $this->exchange();

        $this->assertNotNull($token);
        $this->assertSame('real-at', $token->access_token);
        $this->assertSame(0, DB::table('shopee_requests')->count());
        $this->assertSame(1, DB::table('shopee_access_tokens')->count());
    }

    #[Test]
    public function test_redaction_masks_request_response_and_url()
    {
        config()->set('shopee.logging.redact_secrets', true);
        $token = $this->exchange();

        $this->assertSame('real-at', $token->access_token);
        $this->assertSame('real-rt', $token->refresh_token);

        $row = $this->row();
        $request = json_decode($row->request, true);
        $response = json_decode($row->response, true);

        $this->assertSame('[REDACTED]', $request['code']);
        $this->assertSame('test_partner_id', $request['partner_id']);
        $this->assertSame('[REDACTED]', $response['access_token']);
        $this->assertSame('[REDACTED]', $response['refresh_token']);
        $this->assertSame(14400, $response['expire_in']);

        parse_str(parse_url($row->url, PHP_URL_QUERY), $query);
        $this->assertSame('[REDACTED]', $query['sign']);
        $this->assertArrayHasKey('timestamp', $query);
        $this->assertStringNotContainsString('real-', $row->request . $row->response . $row->url);
    }

    #[Test]
    public function test_redaction_returns_unredacted_result()
    {
        config()->set('shopee.logging.redact_secrets', true);
        Http::fake(['*' => Http::response(['access_token' => 'real-at', 'request_id' => 'r1'])]);

        $result = (new Shopee(partner_id: 'test_partner_id', partner_key: 'test_partner_key'))
            ->payment()->getEscrowDetail(['order_sn' => 'X']);

        $this->assertSame('real-at', $result['access_token']);
        $this->assertSame('[REDACTED]', json_decode($this->row()->response, true)['access_token']);
    }

    #[Test]
    public function test_prunable_disabled_by_default()
    {
        $old = ShopeeRequest::create(['action' => 'X::y', 'url' => 'u']);
        DB::table('shopee_requests')->update(['created_at' => now()->subDays(400)]);

        $this->assertSame(0, (new ShopeeRequest)->prunable()->count());

        config()->set('shopee.logging.prune_after_days', 0);
        $this->assertSame(0, (new ShopeeRequest)->prunable()->count());
    }

    #[Test]
    public function test_prunable_selects_rows_older_than_configured_days()
    {
        config()->set('shopee.logging.prune_after_days', 30);

        ShopeeRequest::create(['action' => 'X::old', 'url' => 'u']);
        ShopeeRequest::create(['action' => 'X::new', 'url' => 'u']);
        DB::table('shopee_requests')->where('action', 'X::old')->update(['created_at' => now()->subDays(31)]);

        $this->assertSame(['X::old'], (new ShopeeRequest)->prunable()->pluck('action')->all());
        $this->assertSame(1, (new ShopeeRequest)->pruneAll());
        $this->assertSame(1, ShopeeRequest::count());
    }
}

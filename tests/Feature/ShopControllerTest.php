<?php

namespace Laraditz\Shopee\Tests\Feature;

use Laraditz\Shopee\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class ShopControllerTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSuccessfulShopeeApi(): void
    {
        Http::fake([
            '*/auth/token/get*' => Http::response([
                'access_token'  => 'test_access_token',
                'refresh_token' => 'test_refresh_token',
                'expire_in'     => 14400,
                'request_id'    => 'req_token_123',
                'error'         => '',
            ], 200),
            '*/shop/get_shop_info*' => Http::response([
                'shop_name'  => 'Test Shop',
                'region'     => 'MY',
                'status'     => 'normal',
                'request_id' => 'req_shop_456',
                'error'      => '',
            ], 200),
        ]);
    }

    private function fakeFailedTokenApi(): void
    {
        Http::fake([
            '*/auth/token/get*' => Http::response([
                'error'      => 'error_auth',
                'message'    => 'Invalid code',
                'request_id' => 'req_fail_789',
            ], 400),
        ]);
    }

    /** @test */
    public function it_renders_built_in_view_when_no_redirect_url_configured()
    {
        $this->app['config']->set('shopee.redirect_url', null);
        $this->fakeSuccessfulShopeeApi();

        $response = $this->get(route('shopee.shops.authorized', [
            'shop_id' => 12345,
            'code'    => 'test_code',
        ]));

        $response->assertViewIs('shopee::shops.authorized');
    }

    /** @test */
    public function it_redirects_to_configured_url_with_data_on_success()
    {
        $configuredUrl = 'https://myapp.com/auth/callback';
        $this->app['config']->set('shopee.redirect_url', $configuredUrl);
        $this->fakeSuccessfulShopeeApi();

        $response = $this->get(route('shopee.shops.authorized', [
            'shop_id'      => 12345,
            'code'         => 'test_code',
            'redirect_url' => $configuredUrl,
        ]));

        $response->assertRedirectContains($configuredUrl);
        $response->assertRedirectContains('shop_id=12345');
        $response->assertRedirectContains('access_token=test_access_token');
        $response->assertRedirectContains('refresh_token=test_refresh_token');
        $response->assertRedirectContains('expires_at=');
    }

    /** @test */
    public function it_ignores_redirect_url_that_does_not_match_config()
    {
        $this->app['config']->set('shopee.redirect_url', 'https://myapp.com/auth/callback');
        $this->fakeSuccessfulShopeeApi();

        $response = $this->get(route('shopee.shops.authorized', [
            'shop_id'      => 12345,
            'code'         => 'test_code',
            'redirect_url' => 'https://evil.com/steal',
        ]));

        // Must fall back to built-in view, never redirect to the unrecognised URL
        $response->assertViewIs('shopee::shops.authorized');
    }

    /** @test */
    public function it_redirects_with_error_param_when_token_fails()
    {
        $configuredUrl = 'https://myapp.com/auth/callback';
        $this->app['config']->set('shopee.redirect_url', $configuredUrl);
        $this->fakeFailedTokenApi();

        $response = $this->get(route('shopee.shops.authorized', [
            'shop_id'      => 12345,
            'code'         => 'test_code',
            'redirect_url' => $configuredUrl,
        ]));

        $response->assertRedirectContains($configuredUrl);
        $response->assertRedirectContains('error=token_failed');
        $response->assertRedirectContains('shop_id=12345');
    }

    /** @test */
    public function it_renders_view_on_token_failure_when_no_redirect_url_configured()
    {
        $this->app['config']->set('shopee.redirect_url', null);
        $this->fakeFailedTokenApi();

        $response = $this->get(route('shopee.shops.authorized', [
            'shop_id' => 12345,
            'code'    => 'test_code',
        ]));

        $response->assertViewIs('shopee::shops.authorized');
    }
}

<?php

namespace Laraditz\Shopee\Tests\Unit;

use Laraditz\Shopee\Shopee;
use Laraditz\Shopee\Tests\TestCase;
use Laraditz\Shopee\Services\ShopService;

class ShopServiceTest extends TestCase
{
    private ShopService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new ShopService(new Shopee(
            partner_id: 'test_partner_id',
            partner_key: 'test_partner_key',
        ));
    }

    /** @test */
    public function it_generates_authorization_url_without_redirect_url_when_config_is_null()
    {
        $this->app['config']->set('shopee.redirect_url', null);

        $url = $this->service->generateAuthorizationURL();

        // The 'redirect' param (callback URL) must not contain redirect_url
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $this->assertStringNotContainsString('redirect_url', $params['redirect']);
        $this->assertStringContainsString('/api/v2/shop/auth_partner', $url);
    }

    /** @test */
    public function it_embeds_redirect_url_inside_redirect_callback_param_when_config_is_set()
    {
        $this->app['config']->set('shopee.redirect_url', 'https://myapp.com/auth/callback');

        $url = $this->service->generateAuthorizationURL();

        // Parse the top-level query string
        parse_str(parse_url($url, PHP_URL_QUERY), $params);

        // redirect_url must NOT be a top-level param on the Shopee auth URL
        $this->assertArrayNotHasKey('redirect_url', $params);

        // It must be embedded inside the 'redirect' callback param value
        // http_build_query encodes the value, so redirect_url=... appears in the raw callback string
        $this->assertStringContainsString('redirect_url=', $params['redirect']);
        $this->assertStringContainsString('myapp.com', $params['redirect']);
    }
}

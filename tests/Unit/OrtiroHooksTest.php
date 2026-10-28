<?php

namespace Laraditz\Shopee\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Laraditz\Shopee\Events\ShopAuthorized;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Shopee;
use Laraditz\Shopee\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrtiroHooksTest extends TestCase
{
    private function shopee(): Shopee
    {
        return new Shopee(partner_id: 'test_partner_id', partner_key: 'test_partner_key');
    }

    private function redirectParam(string $url): string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);

        return $params['redirect'];
    }

    #[Test]
    public function test_authorization_url_default_unchanged()
    {
        $this->app['config']->set('shopee.redirect_url', null);

        $url = $this->shopee()->shop()->generateAuthorizationURL();

        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $this->assertSame(route('shopee.shops.authorized'), $params['redirect']);
        $this->assertSame('test_partner_id', $params['partner_id']);
        $this->assertArrayHasKey('sign', $params);
        $this->assertArrayHasKey('timestamp', $params);
        $this->assertStringContainsString('/api/v2/shop/auth_partner', $url);
    }

    #[Test]
    public function test_authorization_url_uses_given_redirect()
    {
        $this->app['config']->set('shopee.redirect_url', 'https://myapp.com/auth/callback');

        $url = $this->shopee()->shop()->generateAuthorizationURL('https://ortiro.test/cb?state=abc');

        $this->assertSame('https://ortiro.test/cb?state=abc', $this->redirectParam($url));
    }

    #[Test]
    public function test_push_signature_valid()
    {
        $url = 'https://ortiro.test/webhooks/shopee';
        $body = '{"code":3,"shop_id":1}';
        $sig = hash_hmac('sha256', $url . '|' . $body, 'test_partner_key');

        $this->assertTrue($this->shopee()->verifyPushSignature($url, $body, $sig));
        $this->assertTrue($this->shopee()->verifyPushSignature($url, $body, '  ' . strtoupper($sig) . ' '));
    }

    #[Test]
    public function test_push_signature_rejects_tampered_body()
    {
        $url = 'https://ortiro.test/webhooks/shopee';
        $sig = hash_hmac('sha256', $url . '|{"a":1}', 'test_partner_key');

        $this->assertFalse($this->shopee()->verifyPushSignature($url, '{"a":2}', $sig));
    }

    #[Test]
    public function test_push_signature_rejects_empty_header()
    {
        $this->assertFalse($this->shopee()->verifyPushSignature('https://x.test', '{}', ''));
        $this->assertFalse($this->shopee()->verifyPushSignature('https://x.test', '{}', '   '));
    }

    #[Test]
    public function test_access_token_dispatches_shop_authorized()
    {
        Event::fake([ShopAuthorized::class]);
        Http::fake(['*' => Http::response([
            'access_token' => 'at',
            'refresh_token' => 'rt',
            'expire_in' => 14400,
        ])]);

        $shop = ShopeeShop::create(['id' => 12345678, 'code' => 'auth-code']);

        $token = $this->shopee()->auth()->accessToken(12345678);

        $this->assertNotNull($token);
        Event::assertDispatched(
            ShopAuthorized::class,
            fn (ShopAuthorized $e) => $e->shop->is($shop) && $e->accessToken->is($token)
        );
    }
}

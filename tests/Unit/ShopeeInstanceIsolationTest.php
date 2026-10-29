<?php

namespace Laraditz\Shopee\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laraditz\Shopee\Models\ShopeeAccessToken;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Shopee;
use Laraditz\Shopee\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ShopeeInstanceIsolationTest extends TestCase
{
    #[Test]
    public function test_singleton_resolves_without_default_shop_id()
    {
        config()->set('shopee.shop_id', null);
        app()->forgetInstance('shopee');

        $this->assertNull(app('shopee')->getShopId());
    }

    #[Test]
    public function test_services_use_their_own_instance_credentials()
    {
        Http::fake(['*' => Http::response(['response' => ['order_list' => []]], 200)]);

        $shop = ShopeeShop::create(['id' => 999]);
        ShopeeAccessToken::create([
            'entity_type' => ShopeeShop::class,
            'entity_id' => 999,
            'access_token' => 'tok999',
            'refresh_token' => 'ref999',
            'expires_at' => now()->addHour(),
        ]);

        Shopee::make(partner_id: 'other_partner', partner_key: 'other_key', shop_id: '999')
            ->order()
            ->list(999);

        Http::assertSent(function (Request $request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $q);
            $path = parse_url($request->url(), PHP_URL_PATH);
            $expected = hash_hmac(
                'sha256',
                'other_partner' . $path . ($q['timestamp'] ?? '') . 'tok999' . '999',
                'other_key'
            );

            return ($q['partner_id'] ?? null) === 'other_partner'
                && ($q['sign'] ?? null) === $expected;
        });
    }

    #[Test]
    public function test_product_hook_follow_up_does_not_switch_singleton_shop()
    {
        Http::fake([
            '*get_item_list*' => Http::response(['response' => ['item' => [['item_id' => 1, 'item_status' => 'NORMAL']]]], 200),
            '*get_item_base_info*' => Http::response(['response' => ['item_list' => []]], 200),
            '*' => Http::response(['response' => []], 200),
        ]);

        ShopeeShop::create(['id' => 999]);
        ShopeeAccessToken::create([
            'entity_type' => ShopeeShop::class,
            'entity_id' => 999,
            'access_token' => 'tok999',
            'refresh_token' => 'ref999',
            'expires_at' => now()->addHour(),
        ]);

        Shopee::make(shop_id: '999')->product()->getItemList(offset: 0, page_size: 10, item_status: 'NORMAL');

        $this->assertEquals(12345678, app('shopee')->getShopId());
    }
}

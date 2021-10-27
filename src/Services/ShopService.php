<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Enums\ShopStatus;

class ShopService extends BaseService
{
    public function generateAuthorizationURL()
    {
        $partner_id = app('shopee')->getPartnerId();
        $route = 'shop.auth_partner';
        $path = app('shopee')->getPath($route);
        $signature = app('shopee')->helper()->generateSignature($path);

        $query_string = [
            'partner_id' => $partner_id,
            'redirect' => route('shopee.shops.authorized'),
            'sign' => $signature['signature'],
            'timestamp' => $signature['time'],
        ];

        return app('shopee')->getUrl($route, $query_string);
    }

    public function getInfo(int $id)
    {
        $shop = ShopeeShop::findOrFail($id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'shop.get_info';
        $path = app('shopee')->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = app('shopee')->helper()->generateSignature($path, [$access_token, $id]);

        $query_string = [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => $id,
            'sign' => $signature['signature'],
        ];

        return $this->route($route)
            ->queryString($query_string)
            ->execute();
    }

    public function getEnumStatus(string $status)
    {
        return ShopStatus::getValue(ucfirst(strtolower($status))) ?? null;
    }
}

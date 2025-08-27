<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Enums\ShopStatus;
use LogicException;

class ShopService extends BaseService
{
    public function generateAuthorizationURL()
    {
        $partner_id = $this->shopee->getPartnerId();
        $route = 'shop.auth_partner';
        $path = $this->shopee->getPath($route);
        $signature = $this->shopee->generateSignature($path);

        $query_string = [
            'partner_id' => $partner_id,
            'redirect' => route('shopee.shops.authorized'),
            'sign' => $signature['signature'],
            'timestamp' => $signature['time'],
        ];

        return $this->shopee->getUrl($route, $query_string);
    }

    public function getInfo(?int $id = null)
    {
        $shop = $this->shopee->getShop();

        if ($id !== null) {
            $shop = ShopeeShop::findOrFail($id);
        }

        throw_if(!$shop, LogicException::class, __(__('Shop not found.')));

        $partner_id = $this->shopee->getPartnerId();
        $route = 'shop.get_info';
        $path = $this->shopee->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = $this->shopee->generateSignature($path, [$access_token, $shop->id]);

        $query_string = [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => $shop->id,
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

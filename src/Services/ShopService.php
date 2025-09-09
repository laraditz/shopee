<?php

namespace Laraditz\Shopee\Services;

use LogicException;
use Laraditz\Shopee\Enums\ShopStatus;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Models\ShopeeRequest;

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

        return $this->shopee->getUrl($path, $query_string);
    }

    // public function getInfo(?int $id = null)
    // {
    //     $shop = $this->shopee->getShop();

    //     if ($id !== null) {
    //         $shop = ShopeeShop::findOrFail($id);
    //         if ($shop) {
    //             $this->shopee->setShop($shop);
    //         }
    //     }

    //     throw_if(!$shop, LogicException::class, __(__('Shop not found.')));

    //     $route = 'shop.get_info';

    //     return $this->route($route)
    //         ->execute();
    // }

    public function afterGetInfoResponse(ShopeeRequest $request, ?array $result = [])
    {
        if ($result) {
            $status = data_get($result, 'status');

            if ($status) {
                $status = ucfirst(strtolower($status));
            }

            $this->shopee->getShop()?->update([
                'name' => data_get($result, 'shop_name'),
                'region' => data_get($result, 'region'),
                'status' => ShopStatus::tryFromName($status),
            ]);
        }
    }

    public function afterGetShopInfoResponse(ShopeeRequest $request, ?array $result = [])
    {
        if ($result) {
            $status = data_get($result, 'status');

            if ($status) {
                $status = ucfirst(strtolower($status));
            }

            $this->shopee->getShop()?->update([
                'name' => data_get($result, 'shop_name'),
                'region' => data_get($result, 'region'),
                'status' => ShopStatus::tryFromName($status),
            ]);
        }
    }
}

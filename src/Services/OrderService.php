<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeOrder;
use Laraditz\Shopee\Models\ShopeeShop;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderService extends BaseService
{
    public function list(int $shop_id, array $params = [])
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'order.get_list';
        $path = app('shopee')->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = app('shopee')->helper()->generateSignature($path, [$access_token, $shop_id]);

        $query_string = [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => $shop_id,
            'sign' => $signature['signature'],
        ];

        $response = $this->route($route)
            ->queryString($query_string)
            ->payload($params)
            ->execute();

        if ($response) {
            $order_list = data_get($response, 'response.order_list');

            if ($order_list && count($order_list) > 0) {
                foreach ($order_list as $order) {
                    ShopeeOrder::updateOrCreate([
                        'id' => $order['order_sn']
                    ], [
                        'shop_id' => $shop_id
                    ]);
                }
            }

            return data_get($response, 'response');
        }

        return null;
    }

    public function detail(string $order_sn, array $extraFields = [], int $shop_id = null)
    {
        $order = ShopeeOrder::find($order_sn);

        if ($shop_id) {
            $shop = ShopeeShop::find($shop_id);
        } elseif ($order) {
            $shop = $order->shop;
        }

        throw_if(
            !$shop,
            NotFoundHttpException::class,
            'Shop not found.'
        );

        $partner_id = app('shopee')->getPartnerId();
        $route = 'order.get_detail';
        $path = app('shopee')->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = app('shopee')->helper()->generateSignature($path, [$access_token, $shop->id]);

        $query_string = [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => $shop->id,
            'sign' => $signature['signature'],
        ];

        $payload = [
            'order_sn_list' => $order_sn,
        ];

        if (count($extraFields) > 0) {
            $payload['response_optional_fields'] = implode(',', $extraFields);
        }

        $response = $this->route($route)
            ->queryString($query_string)
            ->payload($payload)
            ->execute();

        if ($response) {
            if (!$order && $order_sn && $shop) {
                ShopeeOrder::updateOrCreate([
                    'id' => $order_sn
                ], [
                    'shop_id' => $shop->id,
                    'status' => data_get($response, 'response.order_list.0.order_status'),
                ]);
            }

            return data_get($response, 'response.order_list.0');
        }

        return null;
    }
}

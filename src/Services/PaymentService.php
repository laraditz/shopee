<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeOrder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentService extends BaseService
{
    public function escrowDetail(string $order_sn)
    {
        $order = ShopeeOrder::findOrFail($order_sn);
        throw_if(
            !$order->shop,
            NotFoundHttpException::class,
            'Shop not found.'
        );
        $shop = $order->shop;

        $partner_id = $this->shopee->getPartnerId();
        $route = 'payment.get_escrow_detail';
        $path = $this->shopee->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = $this->shopee->generateSignature($path, [$access_token, $order->shop_id]);

        $query_string = [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => $order->shop_id,
            'sign' => $signature['signature'],
        ];

        $payload = [
            'order_sn' => $order_sn,
        ];

        $response = $this->route($route)
            ->queryString($query_string)
            ->payload($payload)
            ->execute();

        if ($response) {
            return data_get($response, 'response.order_income');
        }

        return null;
    }
}

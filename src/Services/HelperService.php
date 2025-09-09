<?php

namespace Laraditz\Shopee\Services;

class HelperService
{
    public function generateSignature(string $path, array $params = []): array
    {
        $partner_id = config('shopee.partner_id');
        $partner_key = config('shopee.partner_key');
        $time = time();

        $string = sprintf('%s%s%s', $partner_id, $path, $time);

        if (count($params) > 0) {
            $string .= implode('', $params);
        }

        return [
            'signature' => hash_hmac('sha256', $string, $partner_key),
            'time' => $time,
        ];
    }

    public function transformWebhookData(array $data)
    {
        $return = [];

        if (data_get($data, 'data')) {
            $return = array_merge($return, data_get($data, 'data'));
        }

        if (data_get($data, 'shop_id')) {
            $return['shop_id'] = data_get($data, 'shop_id');
        }

        if (data_get($data, 'code')) {
            $return = array_merge(['code' => data_get($data, 'code')], $return);
        }

        $event_name = $this->getEventName(data_get($return, 'code'));
        if ($event_name) {
            $return = array_merge(['event' => $event_name], $return);
        }

        return $return;
    }

    public function getEventName($code): string
    {
        return match ($code) {
            1 => 'shop_authorization',
            2 => 'shop_deauthorization',
            3 => 'order_status_update',
            4 => 'tracking_no',
            5 => 'shopee_updates',
            6 => 'banned_item',
            7 => 'item_promotion',
            8 => 'reserved_stock_change',
            9 => 'promotion_update',
            10 => 'webchat',
            11 => 'video_upload',
            12 => 'openapi_authorization_expiry',
            13 => 'brand_register_result',
            default => 'unregistered_event',
        };
    }
}

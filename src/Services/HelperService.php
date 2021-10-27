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

    public function getEventName($code)
    {
        switch ($code) {
            case 1:
                return 'shop_authorization';
                break;
            case 2:
                return 'shop_deauthorization';
                break;
            case 3:
                return 'order_status_update';
                break;
            case 4:
                return 'tracking_no';
                break;
            case 5:
                return 'shopee_updates';
                break;
            case 6:
                return 'banned_item';
                break;
            case 7:
                return 'item_promotion';
                break;
            case 8:
                return 'reserved_stock_change';
                break;
            case 9:
                return 'promotion_update';
                break;
            case 10:
                return 'webchat';
                break;
            case 11:
                return 'video_upload';
                break;
            case 12:
                return 'openapi_authorization_expiry';
                break;
            case 13:
                return 'brand_register_result';
                break;
            default:
                return 'unregistered_event';
        }
    }
}

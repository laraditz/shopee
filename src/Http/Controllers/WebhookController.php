<?php

namespace Laraditz\Shopee\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Models\ShopeeOrder;
use Laraditz\Shopee\Models\ShopeeRequest;
use Laraditz\Shopee\Enums\EntityType;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Support\Arr;
use Laraditz\Shopee\Events\WebhookReceived;
use Laraditz\Shopee\Models\ShopeeWebhook;

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        // logger()->info('Shopee webhook : Received', $request->all());
        $receivedData = $request->all();

        if ($receivedData) {

            $shopId = data_get($receivedData, 'shop_id');
            $code = data_get($receivedData, 'code');
            $timestamp = data_get($receivedData, 'timestamp');
            $data = data_get($receivedData, 'data');

            ShopeeWebhook::create([
                'shop_id' => $shopId,
                'code' => $code,
                'data' => $data,
                'sent_timestamp' => $timestamp,
            ]);

            // add event name
            $event_name = app('shopee')->helper()->getEventName($code);
            if ($event_name) {
                $receivedData = array_merge(['event' => $event_name], $receivedData);
            }

            event(new WebhookReceived($receivedData));

            logger()->info('Shopee webhook : Received', $receivedData);

            // add order if not exists
            if (data_get($data, 'ordersn') && $shopId && (int) $code === 3) {
                ShopeeOrder::updateOrCreate([
                    'id' => $data['ordersn'],
                    'shop_id' => $shopId,
                ], [
                    'status' => data_get($data, 'status')
                ]);
            }
        } else {
            // no payload received
            // logger()->error('Shopee webhook : No payload');
        }
    }
}

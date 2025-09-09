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

            ShopeeWebhook::create([
                'shop_id' => $shopId,
                'code' => $code,
                'data' => data_get($receivedData, 'data'),
                'sent_timestamp' => $timestamp,
            ]);

            $data = app('shopee')->helper()->transformWebhookData($receivedData);

            event(new WebhookReceived($data));

            // add order if not exists
            if (Arr::has($data, ['ordersn', 'shop_id', 'code']) && (int) $code == 3) {
                ShopeeOrder::updateOrCreate([
                    'id' => $data['ordersn'],
                    'shop_id' => $data['shop_id'],
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

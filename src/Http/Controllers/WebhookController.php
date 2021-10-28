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

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        // logger()->info('Shopee webhook : Received', $request->all());

        if ($request->all()) {
            $shopeeRequest = ShopeeRequest::create([
                'action' => 'webhook',
                'response' => $request->all(),
            ]);

            $data = app('shopee')->helper()->transformWebhookData($request->all());

            logger()->info('WebhookController', $data);

            event(new WebhookReceived($data));

            // add order if not exists
            if (Arr::has($data, ['ordersn', 'shop_id'])) {
                ShopeeOrder::updateOrCreate([
                    'id' => $data['ordersn']
                ], [
                    'shop_id' => $data['shop_id']
                ]);
            }
        } else {
            // no payload received
            logger()->error('Shopee webhook : No payload');
        }
    }
}

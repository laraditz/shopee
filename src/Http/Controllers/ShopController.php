<?php

namespace Laraditz\Shopee\Http\Controllers;

use Illuminate\Http\Request;
use Laraditz\Shopee\Enums\EntityType;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Models\ShopeeAccessToken;

class ShopController extends Controller
{
    public function authorized(Request $request)
    {
        if (!$request->has(['shop_id', 'code'])) {
            abort(401, __('Unauthorized.'));
        }

        // Only accept redirect_url if it exactly matches the configured value.
        // This prevents open redirect attacks via crafted callback URLs.
        $configured = config('shopee.redirect_url');
        $redirect_url = ($configured && $request->redirect_url === $configured)
            ? $configured
            : null;

        $shop = ShopeeShop::updateOrCreate(
            ['id' => $request->shop_id],
            ['code' => $request->code]
        );

        // BaseService::execute() calls $response->throw() internally, which throws
        // RequestException on HTTP error responses (e.g. 400). Catch it so the
        // failure path below can execute rather than producing a 500.
        try {
            $response = app('shopee')->auth()->accessToken($shop->id, EntityType::Shop);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $response = null;
        }

        if ($response && $response instanceof ShopeeAccessToken) {
            app('shopee')->shopId($shop->id)->shop()->getInfo();
            $shop->refresh()->load('accessToken');

            if ($redirect_url) {
                return redirect($redirect_url . '?' . http_build_query([
                    'shop_id'       => $shop->id,
                    'shop_name'     => $shop->name,
                    'region'        => $shop->region,
                    'access_token'  => $shop->accessToken?->access_token,
                    'refresh_token' => $shop->accessToken?->refresh_token,
                    'expires_at'    => $shop->accessToken?->expires_at?->toIso8601String(),
                ]));
            }

            return view('shopee::shops.authorized', [
                'code' => $request->code,
                'shop' => $shop,
            ]);
        }

        if ($redirect_url) {
            return redirect($redirect_url . '?' . http_build_query([
                'error'   => 'token_failed',
                'shop_id' => $shop->id,
            ]));
        }

        return view('shopee::shops.authorized', [
            'code' => $request->code,
            'shop' => $shop,
        ]);
    }
}

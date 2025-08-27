<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Models\ShopeeAccessToken;
use Laraditz\Shopee\Enums\EntityType;

class AuthService extends BaseService
{
    public function accessToken(int $entity_id, string $entity_type = EntityType::Shop): ?ShopeeAccessToken
    {
        $partner_id = $this->shopee->getPartnerId();
        $route = 'auth.token';
        $path = $this->shopee->getPath($route);
        $signature = $this->shopee->generateSignature($path);
        $payload = [];
        $entity = null;

        $query_string = [
            'sign' => $signature['signature'],
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
        ];

        if ($entity_type == EntityType::MainAccount) {
            $payload['main_account_id'] = $entity_id;
        } elseif ($entity_type == EntityType::Shop) {
            $payload['shop_id'] = $entity_id;

            $entity = ShopeeShop::findOrFail($entity_id);
        }

        $payload = array_merge([
            'code' => $entity ? $entity->code : null,
            'partner_id' => $partner_id,
        ], $payload);

        // dd($query_string, $payload, $entity, $partner_id, $path, $signature);

        $response = $this->method('post')
            ->route($route)
            ->queryString($query_string)
            ->payload($payload)
            ->execute();

        if ($response && $entity) {
            $entity->accessToken()->updateOrCreate([], [
                'access_token' => data_get($response, 'access_token'),
                'refresh_token' => data_get($response, 'refresh_token'),
                'expires_at' => now()->addSeconds(data_get($response, 'expire_in')),
            ]);

            return $entity->accessToken;
        }

        return null;
    }

    public function refreshToken(ShopeeAccessToken $shopeeAccessToken): ?ShopeeAccessToken
    {
        $partner_id = app('shopee')->getPartnerId();
        $route = 'auth.refresh_token';
        $path = app('shopee')->getPath($route);
        $signature = app('shopee')->helper()->generateSignature($path);
        $payload = [];

        $query_string = [
            'sign' => $signature['signature'],
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
        ];

        $payload = [
            'refresh_token' => $shopeeAccessToken->refresh_token,
            'partner_id' => $partner_id,
        ];

        if ($shopeeAccessToken->entity instanceof ShopeeShop) {
            $payload['shop_id'] = $shopeeAccessToken->entity_id;
        }

        $response = $this->method('post')
            ->route($route)
            ->queryString($query_string)
            ->payload($payload)
            ->execute();

        if ($response && data_get($response, 'access_token')) {
            $shopeeAccessToken->update([
                'access_token' => data_get($response, 'access_token'),
                'refresh_token' => data_get($response, 'refresh_token'),
                'expires_at' => now()->addSeconds(data_get($response, 'expire_in')),
            ]);

            return $shopeeAccessToken;
        }

        return null;
    }
}

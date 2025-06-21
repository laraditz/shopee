<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeOrder;
use Laraditz\Shopee\Models\ShopeeProduct;
use Laraditz\Shopee\Models\ShopeeShop;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService extends BaseService
{
    public function list(int $shop_id, array $params = [])
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'product.get_list';
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
            $product_list = data_get($response, 'response.item');

            if ($product_list && count($product_list) > 0) {
                foreach ($product_list as $product) {
                    $item_id = data_get($product, 'item_id');
                    $item_status = data_get($product, 'item_status');

                    if ($item_id) {
                        ShopeeProduct::updateOrCreate([
                            'id' => $item_id
                        ], [
                            'shop_id' => $shop_id,
                            'status' => $item_status
                        ]);
                    }

                }
            }

            return data_get($response, 'response');
        }

        return null;

    }

    public function baseInfo(int $shop_id, array $params = [])
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'product.get_base_info';
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

        $item_id_list = data_get($params, 'item_id_list');

        $params['item_id_list'] = is_array($item_id_list) ? implode(',', $item_id_list) : $item_id_list;


        $response = $this->route($route)
            ->queryString($query_string)
            ->payload($params)
            ->execute();

        // dd($response);

        if ($response) {
            return $response;
            // return data_get($response, 'response');
        }

        return null;
    }

    public function extraInfo(int $shop_id, array $item_id_list = [])
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'product.get_extra_info';
        $path = app('shopee')->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = app('shopee')->helper()->generateSignature($path, [$access_token, $shop_id]);

        $query_string = [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => $shop_id,
            'sign' => $signature['signature'],
            'item_id_list' => implode(',', $item_id_list)
        ];


        $response = $this->route($route)
            ->queryString($query_string)
            ->execute();

        // dd($response);

        if ($response) {
            return $response;
            // return data_get($response, 'response');
        }

        return null;
    }

    public function search(int $shop_id, array $params = [])
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'product.search';
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
            return data_get($response, 'response');
        }

        return null;
    }

    public function updateStock(int $shop_id, array $params = [])
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'product.update_stock';
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

        $response = $this->method('post')
            ->route($route)
            ->queryString($query_string)
            ->payload($params)
            ->execute();

        if ($response) {
            return data_get($response, 'response');
        }

        return false;

    }

    public function modelList(int $shop_id, int|string $item_id)
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $route = 'product.get_model_list';
        $path = app('shopee')->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = app('shopee')->helper()->generateSignature($path, [$access_token, $shop_id]);

        $query_string = [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => $shop_id,
            'sign' => $signature['signature'],
            'item_id' => intval($item_id)
        ];

        $response = $this->route($route)
            ->queryString($query_string)
            ->execute();

        if ($response) {
            return data_get($response, 'response');
        }

        return null;
    }

}
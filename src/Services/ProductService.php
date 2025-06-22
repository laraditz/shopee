<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Models\ShopeeProduct;
use Laraditz\Shopee\Models\ShopeeProductModel;
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
            $item_ids = [];

            if ($product_list && count($product_list) > 0) {
                foreach ($product_list as $product) {
                    $item_id = data_get($product, 'item_id');
                    $item_status = data_get($product, 'item_status');

                    if ($item_id) {
                        $item_ids[] = $item_id;

                        ShopeeProduct::updateOrCreate([
                            'id' => $item_id
                        ], [
                            'shop_id' => $shop_id,
                            'status' => $item_status
                        ]);
                    }
                }
            }

            if ($item_ids && count($item_ids) > 0) {
                $items = app('shopee')
                    ->product()
                    ->baseInfo(
                        shop_id: $shop_id,
                        params: [
                            'item_id_list' => $item_ids
                        ]
                    );

                $itemList = data_get($items, 'item_list');

                if ($itemList && is_array($itemList) && count($itemList) > 0) {
                    foreach ($itemList as $item) {
                        $item_id = data_get($item, 'item_id');

                        if ($item_id) {
                            $sku = data_get($product, 'item_sku');

                            ShopeeProduct::updateOrCreate([
                                'id' => $item_id
                            ], [
                                'category_id' => data_get($product, 'category_id'),
                                'name' => data_get($product, 'item_name'),
                                'sku' => $sku && $sku != '' ? $sku : null,
                                'has_model' => data_get($product, 'has_model'),
                            ]);
                        }
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
            return data_get($response, 'response');
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
            return data_get($response, 'response');
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
            $result = data_get($response, 'response');

            $models = data_get($result, 'model');

            if ($models && is_array($models) && count($models) > 0) {
                foreach ($models as $model) {
                    $model_id = data_get($model, 'model_id');
                    $model_name = data_get($model, 'model_name');

                    ShopeeProductModel::updateOrCreate([
                        'id' => $model_id,
                        'product_id' => $item_id
                    ], [
                        'name' => $model_name,
                        'sku' => data_get($model, 'model_sku'),
                        'price_info' => data_get($model, 'price_info'),
                        'stock_info' => data_get($model, 'stock_info_v2'),
                        'status' => data_get($model, 'model_status'),
                        'weight' => data_get($model, 'weight'),
                        'dimension' => data_get($model, 'dimension'),
                    ]);
                }
            }

            return $result;
        }

        return null;
    }

}
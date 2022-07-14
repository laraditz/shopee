<?php

namespace Laraditz\Shopee\Services;

use Illuminate\Support\Str;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Models\ShopeeOrder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Service extends BaseService
{
    protected $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function __call($name, array $args = [])
    {
        $name = Str::snake($name);
        list($shop_id, $params) = $args;
        $route = ($this->name).'.'.$name;

        $response = $this->route($route)
            ->queryString($this->getDefaultParameters($shop_id, $route))
            ->payload($params)
            ->execute();

        return $response;
    }

    protected function getMethod() 
    {
        $route = $this->getRoute();
        return config('shopee.methods.'.$route, 'get');    
    }

    protected function getDefaultParameters($shop_id, $route)
    {
        $shop = ShopeeShop::findOrFail($shop_id);

        $partner_id = app('shopee')->getPartnerId();
        $path = app('shopee')->getPath($route);
        $access_token = data_get($shop, 'accessToken.access_token');
        $signature = app('shopee')->helper()->generateSignature($path, [$access_token, $shop_id]);

        return [
            'partner_id' => $partner_id,
            'timestamp' => $signature['time'],
            'access_token' => $access_token,
            'shop_id' => (int) $shop_id,
            'sign' => $signature['signature'],
        ];
    }
}

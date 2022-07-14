<?php

namespace Laraditz\Shopee;

use Illuminate\Support\Str;
use BadMethodCallException;

class Shopee
{
    private $services = ['auth', 'order', 'helper', 'shop', 'payment', 'product', 'logistics', 'shop_category'];

    public function __call($method, $arguments)
    {
        $property_name = strtolower(Str::snake($method));

        if (in_array($property_name, $this->services)) {
            $reformat_property_name = ucfirst(Str::camel($method));

            $service_name = 'Laraditz\\Shopee\\Services\\' . $reformat_property_name . 'Service';

            if (class_exists($service_name)) {
                return new $service_name;
            } else {
                return new \Laraditz\Shopee\Services\Service($property_name);
            }
        } else {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                get_class(),
                $method
            ));
        }
    }

    public function getUrl(string $path, array $query = [])
    {
        $sandbox_mode = config('shopee.sandbox.mode');
        $route =  config('shopee.routes.' . $path);
        $url = config('shopee.base_url');

        if ($sandbox_mode === true) {
            $url = config('shopee.sandbox.base_url');
        }

        $url .= $route;

        if (count($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    public function getPartnerId()
    {
        return (int)config('shopee.partner_id');
    }

    public function getPath(string $route)
    {
        return config('shopee.routes.' . $route);
    }
}

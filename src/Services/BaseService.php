<?php

namespace Laraditz\Shopee\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laraditz\Shopee\Models\ShopeeRequest;

class BaseService
{
    private $method = 'get';

    private $route;

    private $queryString = [];

    private $payload = [];


    protected function execute()
    {
        $oClass = new \ReflectionClass(get_called_class());
        $service_name = $oClass->getShortName();
        $called_method = $this->getCalledMethod();

        $method = $this->getMethod();
        $url = $this->getUrl();

        if ($this->getRoute() == 'shop.get_info') {
            // dd($url, $this->getPayload());
        }

        $response = Http::asJson();

        if (app()->environment('local')) {
            $response->withoutVerifying();
        }

        $payload = $this->getPayload();

        if ($method == 'get') {
            $payload = array_merge($this->getQueryString(), $this->getPayload());
        }

        $request = ShopeeRequest::create([
            'action' => $service_name . '::' . $called_method,
            'url' => $url,
            'request' => $payload,
        ]);

        $response = $response->$method($url, $payload);

        $response->throw();

        if ($response->successful()) {
            $request->update([
                'response' => $response->json(),
                'request_id' => data_get($response->json(), 'request_id'),
                'error' => data_get($response->json(), 'error')
            ]);

            return $response->json();
        }
    }

    protected function getUrl()
    {
        if ($this->getMethod() === 'get') {
            return app('shopee')->getUrl($this->getRoute());
        } else {
            return app('shopee')->getUrl($this->getRoute(), $this->getQueryString());
        }
    }

    protected function route($route)
    {
        $this->setRoute($route);

        return $this;
    }

    protected function setRoute($route)
    {
        $this->route = $route;
    }

    protected function getRoute()
    {
        return $this->route;
    }

    protected function method($method)
    {
        $this->setMethod($method);

        return $this;
    }

    protected function setMethod($method)
    {
        if ($method) {
            $this->method = strtolower($method);
        }
    }

    protected function getMethod()
    {
        return $this->method;
    }

    public function payload($payload)
    {
        $this->setPayload($payload);

        return $this;
    }

    protected function setPayload($payload)
    {
        $this->payload = $payload;
    }

    protected function getPayload()
    {
        return $this->payload;
    }

    public function queryString($queryString)
    {
        $this->setQueryString($queryString);

        return $this;
    }

    protected function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    protected function getQueryString()
    {
        return $this->queryString;
    }

    public function getCalledMethod()
    {
        $e = new \Exception();
        $trace = $e->getTrace();
        //position 0 would be the line that called this function so we ignore it
        return data_get($trace, '2.function');
    }
}

<?php

namespace Laraditz\Shopee\Services;

use BadMethodCallException;
use Illuminate\Support\Str;
use Laraditz\Shopee\Shopee;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Laraditz\Shopee\Models\ShopeeRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

class BaseService
{
    public string $methodName = '';

    public string $serviceName = '';

    public string $fqcn = '';

    public string $routePath = '';

    public ?PendingRequest $client = null;


    public function __construct(
        public Shopee $shopee,
        private ?string $route = '',
        private ?string $method = 'get',
        private ?array $queryString = [], // for url query string
        private ?array $payload = [], // for body payload
        private null|array|string|int $params = null, // for path variables
    ) {
    }

    public function __call($methodName, $arguments)
    {
        $oClass = new \ReflectionClass(get_called_class());

        $this->fqcn = $oClass->getName();
        $this->serviceName = $oClass->getShortName();
        $this->methodName = $methodName;

        // if method exists, return       
        if (method_exists($this, $methodName)) {
            return $this->$methodName(...$arguments);
        }

        if (in_array(Str::snake($methodName), $this->getAllowedMethods())) {

            if (count($arguments) > 0) {
                $this->setPayload($arguments);
            }

            $this->setRouteFromConfig($this->fqcn, $this->methodName);

            return $this->execute();
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            $this->fqcn,
            $methodName
        ));
    }

    protected function execute()
    {
        $oClass = new \ReflectionClass(get_called_class());
        if (!$this->serviceName) {
            $service_name = $oClass->getShortName();
            $this->serviceName = $service_name;
        }

        if (!$this->methodName) {
            $called_method = $this->getCalledMethod();
            $this->methodName = $called_method;
        }

        $url = $this->getUrl();
        $method = $this->getMethod();

        if ($this->getRoute() == 'shop.get_info') {
            // dd($url, $this->getPayload());
        }

        $response = $this->getClient();

        if (app()->environment('local')) {
            $response->withoutVerifying();
        }

        $payload = $this->getPayload();
        $commonParameters = $this->getCommonParameters();

        if (strtolower($method) != 'get') {
            $this->setQueryString(array_merge($commonParameters, $this->getQueryString()));
            $url = $this->getUrl();
        } else {
            $payload = array_merge($commonParameters, $payload);
            $this->setPayload($payload);
        }

        $request = ShopeeRequest::create([
            'shop_id' => $this->shopee->getShopId(),
            'action' => $this->serviceName . '::' . $this->methodName,
            'url' => $url,
            'request' => $this->getPayload() && count($this->getPayload()) > 0 ? $this->getPayload() : null,
        ]);

        // dd($url, $payload);

        $response = $response->$method($url, $this->getPayload());

        $response->throw(function (Response $response, RequestException $e) use ($request) {
            $result = $response->json();
            $error = data_get($result, 'error');
            $request_id = data_get($result, 'request_id');

            if ($error) {
                $request->update([
                    'response' => $result,
                    'request_id' => $request_id,
                    'error' => $error
                ]);
            } else {
                $request->update([
                    'error' => Str::limit($e->getMessage(), limit: 97)
                ]);
            }
        });

        if ($response->successful()) {
            $result = $response->json();

            $request->update([
                'response' => $result,
                'request_id' => data_get($result, 'request_id'),
                'error' => data_get($result, 'error')
            ]);

            $this->afterResponse(request: $request, result: $result);

            return $result;
        }
    }

    private function afterResponse(ShopeeRequest $request, ?array $result = []): void
    {
        $methodName = 'after' . Str::studly($this->methodName) . 'Response';

        if (method_exists($this, $methodName)) {
            $this->$methodName($request, $result);
        }
    }

    public function getCommonParameters(): array
    {
        $params = [];
        $shop = $this->shopee->getShop();
        $partner_id = $this->shopee->getPartnerId();
        $route = $this->getRoute();
        $path = $this->getRoutePath();
        $access_token = data_get($shop, 'accessToken.access_token');


        if (in_array($route, ['auth.token', 'auth.refresh_token'])) {
            $signature = $this->shopee->generateSignature($path);

            $params = [
                'partner_id' => $partner_id,
                'timestamp' => $signature['time'],
                'sign' => $signature['signature'],
            ];
        } elseif ($partner_id && $shop && $access_token) {
            $signature = $this->shopee->generateSignature($path, [$access_token, $shop?->id]);

            $params = [
                'partner_id' => $partner_id,
                'timestamp' => $signature['time'],
                'access_token' => $access_token,
                'shop_id' => $shop->id,
                'sign' => $signature['signature'],
            ];
        }

        return $params;
    }

    protected function getUrl()
    {
        $params = $this->getParams();
        $route = $this->shopee->getPath($this->getRoute());

        $split = Str::of($route)->explode(' ');

        if (count($split) == 2) {
            $this->setMethod(data_get($split, '0'));
            $this->setRoutePath(data_get($split, '1'));
        } elseif (count($split) == 1) {
            $this->setRoutePath(data_get($split, '0'));
        }

        if ($params) {
            if (is_array($params)) {
                $mappedParams = collect($params)->mapWithKeys(fn($value, $key) => ["{" . $key . "}" => $value]);

                $this->setRoutePath(Str::swap($mappedParams->toArray(), $this->getRoutePath()));
            } elseif (is_string($params) || is_numeric($params)) {
                $this->setRoutePath(str_replace('{id}', $params, $this->getRoutePath()));
            }
        }

        if ($this->getMethod() === 'get') {
            return $this->shopee->getUrl($this->getRoutePath());
        } else {
            return $this->shopee->getUrl($this->getRoutePath(), $this->getQueryString());
        }
    }

    protected function setRoutePath(string $routePath): void
    {
        $this->routePath = $routePath;
    }

    protected function getRoutePath()
    {
        return $this->routePath;
    }

    private function setRouteFromConfig(string $fqcn, string $method): void
    {
        $route_prefix = Str::of($fqcn)->afterLast('\\')->remove('Service')->lower()->value;
        $route_name = Str::of($method)->snake()->value;

        $this->route($route_prefix . '.' . $route_name);
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

    protected function setParams(null|array|string|int $params): void
    {
        $this->params = $params;
    }

    protected function getParams(): null|array|string|int
    {
        return $this->params;
    }

    public function getCalledMethod()
    {
        $e = new \Exception();
        $trace = $e->getTrace();
        //position 0 would be the line that called this function so we ignore it
        return data_get($trace, '2.function');
    }

    protected function getAllowedMethods(): array
    {
        $route_prefix = Str::of($this->serviceName)->remove('Service')->snake()->lower()->value;

        return array_keys(config('shopee.routes.' . $route_prefix) ?? []);
    }

    private function getClient(): PendingRequest
    {
        return Http::asJson();
    }
}

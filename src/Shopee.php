<?php

namespace Laraditz\Shopee;

use Laraditz\Shopee\Models\ShopeeShop;
use LogicException;
use BadMethodCallException;
use Illuminate\Support\Str;

class Shopee
{
    private $services = ['auth', 'order', 'helper', 'shop', 'payment', 'product'];

    private ?ShopeeShop $shop = null;

    public function __construct(
        private ?string $partner_id = null,
        private ?string $partner_key = null,
        private ?string $shop_id = null
    ) {
        $this->setPartnerId($this->partner_id ?? config('shopee.partner_id'));
        $this->setPartnerKey($this->partner_key ?? config('shopee.partner_key'));
        $this->setShopId($this->shop_id ?? config('shopee.shop_id'));
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function __call($method, $arguments)
    {
        throw_if(!$this->getPartnerId(), LogicException::class, __('Missing Partner ID.'));
        throw_if(!$this->getPartnerKey(), LogicException::class, __('Missing Partner Key.'));

        if (count($arguments) > 0) {
            $argumentCollection = collect($arguments);

            try {
                $argumentCollection->keys()->ensure('string');
            } catch (\Throwable $th) {
                // throw $th;
                throw new LogicException(__('Please pass a named arguments in :method method.', ['method' => $method]));
            }

            if ($shop_id = data_get($arguments, 'shop_id')) {
                $this->setShopId($shop_id);
            }
        }

        if (
            ($this->getShop() === null && $this->getShopId())
            || ($this->getShop() && $this->getShop()?->id !== $this->getShopId())
        ) {

            $shopeeShop = ShopeeShop::firstOrCreate(['id' => $this->getShopId()], []);
            if ($shopeeShop) {
                $this->setShop($shopeeShop);
            }
        }

        $property_name = strtolower(Str::snake($method));

        if (in_array($property_name, $this->services)) {
            $reformat_property_name = ucfirst(Str::camel($method));

            $service_name = 'Laraditz\\Shopee\\Services\\' . $reformat_property_name . 'Service';

            return new $service_name($this);
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
        $url = config('shopee.base_url');

        if ($sandbox_mode === true) {
            $url = config('shopee.sandbox.base_url');
        }

        $url .= $path;

        if (count($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    public function generateSignature(string $path, array $params = []): array
    {
        $partner_id = $this->getPartnerId();
        $partner_key = $this->getPartnerKey();
        $time = time();

        $string = sprintf('%s%s%s', $partner_id, $path, $time);

        if (count($params) > 0) {
            $string .= implode('', $params);
        }

        // dd($partner_id, $partner_key, $time, $path, $string, hash_hmac('sha256', $string, $partner_key));

        return [
            'signature' => hash_hmac('sha256', $string, $partner_key),
            'time' => $time,
        ];
    }

    public function getPartnerId()
    {
        return $this->partner_id;
    }

    public function setPartnerId(string|int $partnerId): void
    {
        $this->partner_id = $partnerId;
    }

    public function getPartnerKey()
    {
        return $this->partner_key;
    }

    public function setPartnerKey(string|int $partnerKey): void
    {
        $this->partner_key = $partnerKey;
    }

    public function getShopId(): int|string|null
    {
        return $this->shop_id;
    }

    public function setShopId(string|int $shopId): void
    {
        $this->shop_id = $shopId;
    }

    public function shopId(string|int $shopId): self
    {
        $this->setShopId($shopId);

        return $this;
    }

    public function getShop(): ShopeeShop|null
    {
        return $this->shop;
    }

    public function setShop(ShopeeShop $shop): void
    {
        $this->shop = $shop;
    }

    public function getPath(string $route)
    {
        return config('shopee.routes.' . $route);
    }
}

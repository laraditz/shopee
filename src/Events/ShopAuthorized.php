<?php

namespace Laraditz\Shopee\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Laraditz\Shopee\Models\ShopeeAccessToken;
use Laraditz\Shopee\Models\ShopeeShop;

class ShopAuthorized
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ShopeeShop $shop,
        public readonly ShopeeAccessToken $accessToken,
    ) {
    }
}

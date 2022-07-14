<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'partner_id' => env('SHOPEE_PARTNER_ID'),
    'shop_id' => env('SHOPEE_SHOP_ID'),
    'partner_key' => env('SHOPEE_PARTNER_KEY'),
    'sandbox' => [
        'mode' => env('SHOPEE_SANDBOX_MODE', false),
        'base_url' => 'https://partner.test-stable.shopeemobile.com',
    ],
    'base_url' => 'https://partner.shopeemobile.com',
    'routes' => [
        'prefix' => 'shopee',
        'auth' => [
            'token' => '/api/v2/auth/token/get',
            'refresh_token' => '/api/v2/auth/access_token/get',
        ],
        'shop' => [
            'auth_partner' => '/api/v2/shop/auth_partner',
            'get_info' => '/api/v2/shop/get_shop_info',
        ],
        'order' => [
            'get_list' => '/api/v2/order/get_order_list',
            'get_detail' => '/api/v2/order/get_order_detail',
        ],
        'payment' => [
            'get_escrow_detail' => '/api/v2/payment/get_escrow_detail',
        ],
        'product' => [
            'add_item' => '/api/v2/product/add_item',
            'get_item_list' => '/api/v2/product/get_item_list',
            'get_item_base_info' => '/api/v2/product/get_item_base_info',
            'get_item_extra_info' => '/api/v2/product/get_item_extra_info',
            'get_attributes' => '/api/v2/product/get_attributes',
            'update_stock' => '/api/v2/product/update_stock',
            'update_price' => '/api/v2/product/update_price',
            'update_item' => '/api/v2/product/update_item',
        ],
        'logistics' => [
            'get_channel_list' => '/api/v2/logistics/get_channel_list',
        ],
        'shop_category' => [
            'get_shop_category_list' => '/api/v2/shop_category/get_shop_category_list',
        ],
    ],
    'methods' => [
        'shop_category' => [
            'get_shop_category_list' => 'get',
        ],
        'logistics' => [
            'get_channel_list' => 'get',
        ],
        'product' => [
            'add_item' => 'post',
            'get_item_list' => 'get',
            'get_item_base_info' => 'get',
            'get_item_extra_info' => 'get',
            'get_attributes' => 'get',
            'update_stock' => 'post',
            'update_price' => 'post',
            'update_item' => 'post',
        ],
    ],
    'middleware' => ['api'],
];

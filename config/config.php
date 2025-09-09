<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'partner_id' => env('SHOPEE_PARTNER_ID'),
    'partner_key' => env('SHOPEE_PARTNER_KEY'),
    'shop_id' => env('SHOPEE_SHOP_ID'),
    'sandbox' => [
        'mode' => env('SHOPEE_SANDBOX_MODE', false),
        'base_url' => 'https://openplatform.sandbox.test-stable.shopee.sg',
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
            //use naming same as shopee 
            'get_order_list' => '/api/v2/order/get_order_list',
            'get_order_detail' => '/api/v2/order/get_order_detail',
        ],
        'payment' => [
            'get_escrow_detail' => '/api/v2/payment/get_escrow_detail',
        ],
        'product' => [
            'get_list' => '/api/v2/product/get_item_list',
            'get_base_info' => '/api/v2/product/get_item_base_info',
            'get_extra_info' => '/api/v2/product/get_item_extra_info',
            'get_model_list' => '/api/v2/product/get_model_list',
            'update_stock' => 'POST /api/v2/product/update_stock',
            'search' => '/api/v2/product/search_item',
            //use naming same as shopee 
            'get_item_list' => '/api/v2/product/get_item_list',
            'get_item_base_info' => '/api/v2/product/get_item_base_info',
            'get_item_extra_info' => '/api/v2/product/get_item_extra_info',
            'search_item' => '/api/v2/product/search_item',
        ]
    ],
    'middleware' => ['api'],
];

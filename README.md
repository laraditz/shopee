# Laravel Shopee

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laraditz/shopee.svg?style=flat-square)](https://packagist.org/packages/laraditz/shopee)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/shopee.svg?style=flat-square)](https://packagist.org/packages/laraditz/shopee)
![GitHub Actions](https://github.com/laraditz/shopee/actions/workflows/main.yml/badge.svg)

Laravel package for interacting with Shopee API.

## Installation

You can install the package via composer:

```bash
composer require laraditz/shopee
```

## Before Start

Configure your variables in your `.env` (recommended) or you can publish the config file and change it there.

```
SHOPEE_SANDBOX_MODE=true # true or false for sandbox mode
SHOPEE_PARTNER_ID=<your_shopee_partner_id>
SHOPEE_PARTNER_KEY=<your_shopee_partner_id>
```

(Optional) You can publish the config file via this command:

```bash
php artisan vendor:publish --provider="Laraditz\Shopee\ShopeeServiceProvider" --tag="config"
```

Run the migration command to create the necessary database table.

```bash
php artisan migrate
```

## Available Methods

Below is a list of all available methods in this SDK. For detailed usage, please refer to the [Developer’s Guide](https://open.shopee.com/developer-guide/4) and the [API Reference](https://open.shopee.com/documents/v2/v2.product.get_category?module=89&type=1). Each method name corresponds to its respective API endpoint (converted from `snake_case` → `camelCase`), and all parameters follow the exact definitions provided in the API reference.

### Authorization and Authentication Service `auth()`

| Method           | Description                             | Parameters                                                    |
| ---------------- | --------------------------------------- | ------------------------------------------------------------- |
| `accessToken()`  | Generate access token.                  | `code`, `partner_id`, `shop_id` or `main_account_id`          |
| `refreshToken()` | Refresh access token before it expired. | `refresh_token`, `partner_id`, `shop_id` or `main_account_id` |

### Shop Service `shop()`

| Method                       | Description                                       |
| ---------------------------- | ------------------------------------------------- |
| `generateAuthorizationURL()` | Get shop authorization URL for shop to authorize. |
| `getShopInfo()`              | Get shop information.                             |

### Product Service `product()`

| Method               | Description                                                            | Parameters                                                                 |
| -------------------- | ---------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| `getItemList()`      | Get a list of items.                                                   | `offset`, `page_size`, `item_status`, `update_time_from`, `update_time_to` |
| `getItemBaseInfo()`  | Get basic info of item by item_id list.                                | `item_id_list`, `need_tax_info`, `need_complaint_policy`                   |
| `getItemExtraInfo()` | Get extra info of item by item_id list.                                | `item_id_list`                                                             |
| `getModelList()`     | Get model list of an item.                                             | `item_id`                                                                  |
| `searchItem()`       | Use this call to search item.                                          | `item_name`, `item_sku`, `item_status`, `offset`, `page_size` and more     |
| `updateStock()`      | Update one item_id for each call, support updating multiple model_ids. | `item_id`, `stock_list`                                                    |

### Order Service `order()`

| Method             | Description                                  | Parameters                                                                  |
| ------------------ | -------------------------------------------- | --------------------------------------------------------------------------- |
| `getOrderList()`   | Get an order list from specified date range. | `time_range_field`, `time_from`, `time_to`, `page_size`, `cursor` and more  |
| `getOrderDetail()` | Get an order detail by order SN.             | `order_sn_list`, `request_order_status_pending`, `response_optional_fields` |

### Payment Service `payment()`

| Method              | Description                            | Parameters |
| ------------------- | -------------------------------------- | ---------- |
| `getEscrowDetail()` | Get the accounting detail of an order. | `order_sn` |

## Usage

You may call the method by chaining the service name before calling the method name.

```php
use Laraditz\Shopee\Facades\Shopee;

Shopee::order()->getOrderDetail(order_sn_list: '211020BNFYMXXX');

// or using service container
app('shopee')->order()->getOrderDetail(order_sn_list: '211020BNFYMXXX');

```

## Event

This package also provide an event to allow your application to listen for Shopee web push. You can create your listener and register it under event below.

| Event                                  | Description                         |
| -------------------------------------- | ----------------------------------- |
| Laraditz\Shopee\Events\WebhookReceived | Receive a push content from Shopee. |

## Webhook URL

You may setup the URL below on shopee open API dashboard so that Shopee will push all content update to this url and trigger the `WebhookReceived` event above.

```
https://your-app-url/shopee/webhooks
```

## Commands

```bash
shopee:flush-expired-token    Flush expired access token.
shopee:refresh-token          Refresh existing access token before it expired.
```

As Shopee access token expired in 4 hours, you may want to set `shopee:refresh-token` on scheduler and run it before it expires to refresh the access token. Otherwise, you need to reauthorize the shop and generate a new access token.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email raditzfarhan@gmail.com instead of using the issue tracker.

## Credits

- [Raditz Farhan](https://github.com/laraditz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

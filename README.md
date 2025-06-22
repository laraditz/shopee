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

Below are all methods available under this package.

| Service name | Method name                | Description                                                            |
| ------------ | -------------------------- | ---------------------------------------------------------------------- |
| auth()       | accessToken()              | Generate access token.                                                 |
|              | refreshToken()             | Refresh access token before it expired.                                |
| shop()       | generateAuthorizationURL() | Get shop authorization URL for shop to authorize.                      |
|              | getInfo()                  | Get shop information.                                                  |
| order()      | list()                     | Get an order list from specified date range.                           |
|              | detail()                   | Get an order detail by order SN.                                       |
| payment()    | escrowDetail()             | Get the accounting detail of an order.                                 |
| product()    | list()                     | Get a list of items.                                                   |
|              | baseInfo()                 | Get basic info of item by item_id list.                                |
|              | extraInfo()                | Get extra info of item by item_id list.                                |
|              | search()                   | Use this call to search item.                                          |
|              | updateStock()              | Update one item_id for each call, support updating multiple model_ids. |

## Usage

You may call the method by chaining the service name before calling the method name.

```php
// Using service container
app('shopee')->order()->detail('211020BNFYMXXX');

// Using facade
\Shopee::order()->detail('211020BNFYMXXX');
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

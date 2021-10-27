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

| Service name      | Method name               | Description  
|-------------------|---------------------------|------------------------------
| auth()            | accessToken()             | Generate access token.  
|                   | refreshToken()            | Refresh access token before it expired. 
| order()           | list()                    | Get an order list from specified date range.  
|                   | detail()                  | Get an order detail by order SN.  
| payment()         | escrowDetail()            | Get the accounting detail of an order.  
| shop()            | generateAuthorizationURL()| Get shop authorization URL for shop to authorize.  
|                   | getInfo()                 | Get shop information.  


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

| Event                                     |  Description  
|-------------------------------------------|-----------------------|
| Laraditz\Shopee\Events\WebhookReceived    | Receive a push content from Shopee. 

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email raditzfarhan@gmail.com instead of using the issue tracker.

## Credits

-   [Raditz Farhan](https://github.com/laraditz)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

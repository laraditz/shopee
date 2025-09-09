# Laravel Shopee

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laraditz/shopee.svg?style=flat-square)](https://packagist.org/packages/laraditz/shopee)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/shopee.svg?style=flat-square)](https://packagist.org/packages/laraditz/shopee)
[![License](https://img.shields.io/packagist/l/laraditz/my-invois.svg?style=flat-square)](./LICENSE.md)
![GitHub Actions](https://github.com/laraditz/shopee/actions/workflows/main.yml/badge.svg)

A comprehensive Laravel package for seamlessly integrating with the Shopee Open Platform API. This package provides an elegant, fluent interface for managing shops, products, orders, and payments on Shopee's marketplace.

<a href="https://www.buymeacoffee.com/raditzfarhan" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 50px !important;width: 200px !important;" ></a>

## Installation

You can install the package via composer:

```bash
composer require laraditz/shopee
```

## Before Starting

Configure your Shopee API credentials in your `.env` file (recommended) or publish and modify the config file.

```env
SHOPEE_SANDBOX_MODE=true # Set to false for production
SHOPEE_PARTNER_ID=<your_shopee_partner_id>
SHOPEE_PARTNER_KEY=<your_shopee_partner_key>
SHOPEE_SHOP_ID=<your_shopee_shop_id>
```

(Optional) You can publish the config file via this command:

```bash
php artisan vendor:publish --provider="Laraditz\Shopee\ShopeeServiceProvider" --tag="config"
```

Run the migration command to create the necessary database tables for storing shop data, access tokens, and API request logs.

```bash
php artisan migrate
```

## Available Services & Methods

Below is a list of all available methods in this SDK. For detailed usage, please refer to the [Developer’s Guide](https://open.shopee.com/developer-guide/4) and the [API Reference](https://open.shopee.com/documents/v2/v2.product.get_category?module=89&type=1). This package organizes Shopee API endpoints into logical services. Each method name corresponds to its respective API endpoint (converted from `snake_case` → `camelCase`), and all parameters follow the exact definitions provided in the API reference.

> **Note:** All method parameters must be passed as named arguments, not positional arguments.

### 🔐 Authorization and Authentication Service `auth()`

Handles OAuth 2.0 authentication flow and token management.

| Method           | Description                                      | Parameters                                                    |
| ---------------- | ------------------------------------------------ | ------------------------------------------------------------- |
| `accessToken()`  | Generate access token from authorization code    | `code`, `partner_id`, `shop_id` or `main_account_id`          |
| `refreshToken()` | Refresh access token before expiration (4 hours) | `refresh_token`, `partner_id`, `shop_id` or `main_account_id` |

### 🏪 Shop Service `shop()`

Manages shop information and authorization processes.

| Method                       | Description                                        |
| ---------------------------- | -------------------------------------------------- |
| `generateAuthorizationURL()` | Generate authorization URL for shop authorization  |
| `getShopInfo()`              | Retrieve comprehensive shop information and status |

### 📦 Product Service `product()`

Comprehensive product and inventory management capabilities.

| Method               | Description                                                 | Parameters                                                                 |
| -------------------- | ----------------------------------------------------------- | -------------------------------------------------------------------------- |
| `getItemList()`      | Retrieve paginated list of shop items with filters          | `offset`, `page_size`, `item_status`, `update_time_from`, `update_time_to` |
| `getItemBaseInfo()`  | Get basic product information including pricing and status  | `item_id_list`, `need_tax_info`, `need_complaint_policy`                   |
| `getItemExtraInfo()` | Get extended product details like dimensions and attributes | `item_id_list`                                                             |
| `getModelList()`     | Retrieve all variants/models for a specific product         | `item_id`                                                                  |
| `searchItem()`       | Search products by name, SKU, or status with pagination     | `item_name`, `item_sku`, `item_status`, `offset`, `page_size` and more     |
| `updateStock()`      | Update inventory levels for product variants in bulk        | `item_id`, `stock_list`                                                    |

### 🛒 Order Service `order()`

Handles order management and retrieval with detailed tracking information.

| Method             | Description                                            | Parameters                                                                  |
| ------------------ | ------------------------------------------------------ | --------------------------------------------------------------------------- |
| `getOrderList()`   | Retrieve paginated orders within specified date range  | `time_range_field`, `time_from`, `time_to`, `page_size`, `cursor` and more  |
| `getOrderDetail()` | Get comprehensive order details by order serial number | `order_sn_list`, `request_order_status_pending`, `response_optional_fields` |

### 💰 Payment Service `payment()`

Manages payment and financial transaction details.

| Method              | Description                                                 | Parameters |
| ------------------- | ----------------------------------------------------------- | ---------- |
| `getEscrowDetail()` | Retrieve detailed escrow and payment information for orders | `order_sn` |

## Usage Examples

The package provides a fluent, chainable API interface. Access services by chaining the service name before calling the method.

### Basic Usage

```php
use Laraditz\Shopee\Facades\Shopee;

// Get order details
$orderDetails = Shopee::order()->getOrderDetail(
    order_sn_list: '211020BNFYMXXX,211020BNFYXXX2'
);

// Get shop information
$shopInfo = Shopee::shop()->getShopInfo();

// Search products
$products = Shopee::product()->searchItem(
    item_name: 'smartphone',
    page_size: 20,
    offset: 0
);

// Alternative: using service container
$orders = app('shopee')->order()->getOrderList(
    time_range_field: 'create_time',
    time_from: strtotime('-30 days'),
    time_to: time(),
    page_size: 50
);
```

### Multi-Shop Support

By default, the package uses `SHOPEE_SHOP_ID` from your `.env` file. For multi-shop applications, specify the shop ID per request:

```php
use Laraditz\Shopee\Facades\Shopee;

// Method 1: Using make() with shop_id
$products = Shopee::make(shop_id: '2257XXXXX')
    ->product()
    ->getItemList(
        offset: 0,
        page_size: 10,
        item_status: 'NORMAL'
    );

// Method 2: Using shopId() method
$orders = Shopee::shopId('2257XXXXX')
    ->order()
    ->getOrderList(
        time_range_field: 'create_time',
        time_from: strtotime('-7 days'),
        time_to: time()
    );
```

### Error Handling

```php
use Laraditz\Shopee\Facades\Shopee;
use Illuminate\Http\Client\RequestException;

try {
    $result = Shopee::product()->updateStock(
        item_id: 123456789,
        stock_list: [
            [
                'model_id' => 123123123,
                'seller_stock' => [
                    [
                        'location_id' => 'MYZ',
                        'stock' => 100,
                    ]
                ],
            ]
        ]
    );
} catch (RequestException $e) {
    // Handle HTTP/network errors
    logger()->error('Request failed: ' . $e->getMessage());
}
```

## Webhook Integration

This package provides comprehensive webhook support for real-time notifications from Shopee. Refer to [Push Mecahnism](https://open.shopee.com/push-mechanism/5) documentation for more details.

### Event Handling

Create listeners for webhook events to automatically process updates from Shopee:

| Event                                    | Description                                             |
| ---------------------------------------- | ------------------------------------------------------- |
| `Laraditz\Shopee\Events\WebhookReceived` | Triggered when receiving push notifications from Shopee |

### Setting Up Webhooks

**Configure Webhook URL**: In your Shopee Open Platform dashboard, set the webhook URL to:

```
https://your-app-url/shopee/webhooks
```

**Create Event Listeners to Handle Webhook Data**: Create a listener to process incoming data:

```php
<?php

namespace App\Listeners;

use Laraditz\Shopee\Events\WebhookReceived;

class YourWebhookListener
{
    public function handle(WebhookReceived $event)
    {
        $webhookData = $event->data;

        // Process order updates, product changes, etc.
        if ($webhookData['event'] === 'order_status_update') {
            // Handle order status changes
        }
    }
}
```

**Register Event Listeners**: Register listeners in your `EventServiceProvider` (Laravel 10 and below):

```php
use Laraditz\Shopee\Events\WebhookReceived;

protected $listen = [
    WebhookReceived::class => [
        YourWebhookListener::class,
    ],
];
```

## Artisan Commands

The package provides convenient Artisan commands for token management:

```bash
# Remove expired access tokens from database
php artisan shopee:flush-expired-token

# Refresh existing access tokens before expiration
php artisan shopee:refresh-token
```

### Automated Token Refresh

Since Shopee access tokens expire every 4 hours, it's recommended to schedule the refresh command to run automatically. Add this to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Refresh tokens every 3 hours to prevent expiration
    $schedule->command('shopee:refresh-token')
             ->everyThreeHours()
             ->withoutOverlapping();

    // Clean up expired tokens daily
    $schedule->command('shopee:flush-expired-token')
             ->daily();
}
```

> **Important**: Without automatic refresh, expired tokens will require shop reauthorization and manual token generation.

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

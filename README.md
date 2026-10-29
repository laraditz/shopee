# Laravel Shopee

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laraditz/shopee.svg?style=flat-square)](https://packagist.org/packages/laraditz/shopee)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/shopee.svg?style=flat-square)](https://packagist.org/packages/laraditz/shopee)
[![License](https://img.shields.io/packagist/l/laraditz/shopee?style=flat-square)](./LICENSE.md)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/laraditz/shopee/main.yml?branch=master&label=Tests)](https://github.com/laraditz/shopee/actions?query=workflow%3ATests+branch%3Amaster)

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
SHOPEE_REDIRECT_URL=<your_redirect_url> # Optional — see Authorization Flow
```

(Optional) You can publish the config file via this command:

```bash
php artisan vendor:publish --provider="Laraditz\Shopee\ShopeeServiceProvider" --tag="config"
```

Publish and run the migrations to create the necessary database tables for storing shop data, access tokens, and API request logs.

```bash
php artisan vendor:publish --provider="Laraditz\Shopee\ShopeeServiceProvider" --tag="migrations"
php artisan migrate
```

## Authorization Flow

To authorize a Shopee shop with your app:

1. Visit [Shopee Open Platform Console](https://open.shopee.com/).
2. On your App List, click `Authorize` link next to the `Test/Live Partner_id`. Alternatively, you can generate the authorization URL using the `generateAuthorizationURL()` method.
3. Make sure the domain for authorized redirect URL already been whitelisted beforehand.
4. Enter `https://your-app-url/shopee/shops/authorized` as the Redirect URL and click Next.
5. Proceed to login using the seller account that you want to authorized to be use for the app.
6. Shopee redirects back to `https://your-app-url/shopee/shops/authorized`.
7. Package automatically handles the authorization code exchange and token storage.
8. Shop is now ready for API calls.

### Custom Redirect After Authorization

By default, the package renders a built-in success page after authorization. To redirect to your own URL instead, set `SHOPEE_REDIRECT_URL` in your `.env`:

```env
SHOPEE_REDIRECT_URL=https://your-app-url/shopee/callback
```

On success, the package redirects to your URL with the following query parameters:

| Parameter       | Description                   |
| --------------- | ----------------------------- |
| `shop_id`       | Shopee shop ID                |
| `shop_name`     | Shop display name             |
| `region`        | Shop region (e.g. `MY`, `SG`) |
| `access_token`  | OAuth access token            |
| `refresh_token` | OAuth refresh token           |
| `expires_at`    | Token expiry (ISO 8601)       |

On failure (e.g. token exchange error), the redirect includes:

| Parameter | Value          |
| --------- | -------------- |
| `error`   | `token_failed` |
| `shop_id` | Shopee shop ID |

> **Security:** `SHOPEE_REDIRECT_URL` must be an exact absolute URL without query strings. Only requests carrying this exact value are honoured — any other value is silently discarded, preventing open redirect attacks. Use HTTPS in production as tokens are passed in the URL.

## Available Services & Methods

Below is a list of all available methods in this SDK. For detailed usage, please refer to the [Developer’s Guide](https://open.shopee.com/developer-guide/4) and the [API Reference](https://open.shopee.com/documents/v2/v2.product.get_category?module=89&type=1). This package organizes Shopee API endpoints into logical services. Each method name corresponds to its respective API endpoint (converted from `snake_case` → `camelCase`), and all parameters follow the exact definitions provided in the API reference.

> **Note:** All method parameters must be passed as named arguments, not positional arguments.

### 🔐 Authorization and Authentication Service `auth()`

Handles OAuth 2.0 authentication flow and token management.

| Method           | Description                                      | Parameters                            |
| ---------------- | ------------------------------------------------ | ------------------------------------- |
| `accessToken()`  | Generate access token from authorization code    | `entity_id`, EntityType `entity_type` |
| `refreshToken()` | Refresh access token before expiration (4 hours) | ShopeeAccessToken `shopeeAccessToken` |

### 🏪 Shop Service `shop()`

Manages shop information and authorization processes.

| Method                       | Description                                        |
| ---------------------------- | -------------------------------------------------- |
| `generateAuthorizationURL()` | Generate authorization URL for shop authorization  |
| `getShopInfo()`              | Retrieve comprehensive shop information and status |

### 📦 Product Service `product()`

Comprehensive product and inventory management capabilities.

| Method               | Description                                                 | Parameters                                                                                                                                         |
| -------------------- | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| `getItemList()`      | Retrieve paginated list of shop items with filters          | `offset`, `page_size`, `item_status`, `update_time_from`, `update_time_to`                                                                         |
| `getItemBaseInfo()`  | Get basic product information including pricing and status  | `item_id_list`, `need_tax_info`, `need_complaint_policy`                                                                                           |
| `getItemExtraInfo()` | Get extended product details like dimensions and attributes | `item_id_list`                                                                                                                                     |
| `getModelList()`     | Retrieve all variants/models for a specific product         | `item_id`                                                                                                                                          |
| `searchItem()`       | Search products by name, SKU, or status with pagination     | `item_name`, `item_sku`, `item_status`, `offset`, `page_size` and more                                                                             |
| `updateStock()`      | Update inventory levels for product variants in bulk        | `item_id`, `stock_list`                                                                                                                            |
| `addItem()`          | Create a new product listing on Shopee                      | Refer to [Shopee API Reference](https://open.shopee.com/documents/v2/v2.product.add_item?module=89&type=1)                                         |
| `updateItem()`       | Update an existing product listing                          | `item_id` and any fields to update - refer to [Shopee API Reference](https://open.shopee.com/documents/v2/v2.product.update_item?module=89&type=1) |
| `deleteItem()`       | Delete a product listing                                    | `item_id`                                                                                                                                          |

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

### ↩️ Returns Service `returns()`

Retrieves buyer return and refund requests. Each call also syncs a lightweight record (status, negotiation/proof/compensation status, refund amount, currency and Shopee timestamps) into the `shopee_returns` table via the `ShopeeReturn` model. Buyer personal data is not stored.

| Method              | Description                                          | Parameters                                                                                                                      |
| ------------------- | ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| `getReturnList()`   | Retrieve paginated list of returns with filters      | `page_no`, `page_size`, `create_time_from`, `create_time_to`, `update_time_from`, `update_time_to`, `status` and more - refer to [Shopee API Reference](https://open.shopee.com/documents/v2/v2.returns.get_return_list?module=102&type=1) |
| `getReturnDetail()` | Get detailed information of a return                 | `return_sn` - refer to [Shopee API Reference](https://open.shopee.com/documents/v2/v2.returns.get_return_detail?module=102&type=1) |

```php
$returns = Shopee::returns()->getReturnList(page_no: 0, page_size: 50, update_time_from: now()->subDays(7)->timestamp, update_time_to: now()->timestamp);

$return = Shopee::returns()->getReturnDetail(return_sn: '2209010001');

// Synced records
$order->returns;   // ShopeeOrder hasMany ShopeeReturn
$shop->returns;    // ShopeeShop hasMany ShopeeReturn
```

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

// Add a new product
$newProduct = Shopee::product()->addItem(
    item_name: 'My Product',
    description: 'Product description',
    original_price: 29.90,
    normal_stock: 100,
    weight: 0.5,
    item_sku: 'SKU-001',
    category_id: 100001,
    // ... refer to Shopee API Reference for full parameter list
);

// Update an existing product
Shopee::product()->updateItem(
    item_id: 123456789,
    item_name: 'Updated Product Name',
    original_price: 24.90,
);

// Delete a product
Shopee::product()->deleteItem(item_id: 123456789);

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

## Multi-tenant / hardening options (opt-in)

None of these change default behaviour. Existing code keeps working unchanged.

### Authorization URL with a custom redirect

```php
Shopee::make()->shop()->generateAuthorizationURL(?string $redirect = null): string
```

With no argument the URL is built exactly as before (the `shopee.shops.authorized` route, plus `SHOPEE_REDIRECT_URL` when set). When `$redirect` is given it is sent as the `redirect` parameter exactly as passed (query string included), and the route and `redirect_url` logic is skipped.

### Verifying Shopee push signatures

```php
Shopee::make()->verifyPushSignature(string $url, string $rawBody, string $authorization): bool
```

Returns `true` when `$authorization` (the `Authorization` header) equals `hash_hmac('sha256', $url . '|' . $rawBody, partner_key)`, compared in constant time. `$url` is the full push URL registered with Shopee and `$rawBody` the unparsed request body. An empty header returns `false`.

### ShopAuthorized event

`Laraditz\Shopee\Events\ShopAuthorized` is dispatched by `auth()->accessToken()` right after a shop's token is saved. It exposes public readonly `ShopeeShop $shop` and `ShopeeAccessToken $accessToken`.

### Token encryption

```php
// config/shopee.php
'encrypt_tokens' => env('SHOPEE_ENCRYPT_TOKENS', false),
```

Default `false`: tokens are stored as plaintext, exactly as before. When `true`, `access_token` and `refresh_token` are encrypted with Laravel's `Crypt` (so `APP_KEY` must stay stable) when written through the `ShopeeAccessToken` model. Reads work for plaintext rows, encrypted rows, and after the flag is switched off again. A published config file without this key behaves as `false`.

To encrypt rows that already exist, enable the flag and run:

```bash
php artisan shopee:encrypt-tokens
```

It prints `Encrypted N value(s).` and is safe to run again (the second run reports 0). With the flag off it prints `Enable shopee.encrypt_tokens first.` and exits with code 1. Always write tokens through the Eloquent model; query-builder updates skip the cast.

- If you rotate `APP_KEY`, keep the old key in `APP_PREVIOUS_KEYS` so existing encrypted values still decrypt, and do not run `shopee:encrypt-tokens` until then: it treats any value it cannot decrypt with the current key as plaintext and would encrypt it a second time.

### Request log controls

```php
// config/shopee.php
'logging' => [
    'requests' => env('SHOPEE_LOG_REQUESTS', true),
    'redact_secrets' => env('SHOPEE_LOG_REDACT_SECRETS', false),
    'prune_after_days' => env('SHOPEE_LOG_PRUNE_AFTER_DAYS'),
],
```

Defaults keep today's behaviour: every API call is saved to `shopee_requests` unredacted. A published config file without the `logging` block behaves the same way.

- `requests` = `false`: no `shopee_requests` row is written. After-response hooks still run and receive an unsaved `ShopeeRequest` model with `shop_id`, `url` and `request` filled, so token exchanges and product syncs keep working.
- `redact_secrets` = `true`: before saving, `access_token`, `refresh_token`, `code` and `sign` are replaced with `[REDACTED]` in the `request` payload and in the URL query string, and `access_token` and `refresh_token` in the `response`, at any depth. Only the stored log changes; the value returned by the API method and passed to hooks is unredacted.
- `prune_after_days`: a positive integer makes `ShopeeRequest` and `ShopeeWebhook` rows older than that many days prunable (`MassPrunable`). Unset, `0` or non-numeric prunes nothing.

Laravel's `model:prune` only auto-discovers models in `app/Models`, so package models are not pruned unless you schedule them yourself with `--model=`:

```php
Schedule::command('model:prune', [
    '--model' => [
        \Laraditz\Shopee\Models\ShopeeRequest::class,
        \Laraditz\Shopee\Models\ShopeeWebhook::class,
    ],
])->daily();
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

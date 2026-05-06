# Changelog

All notable changes to `laraditz/shopee` package will be documented in this file

## 1.1.10 - 2026-05-06

### Changed

- Move `RefreshDatabase` to base `TestCase` and use `defineDatabaseMigrations` for migration timing.

## 1.1.9 - 2026-05-06

### Changed

- Update `ShopStatus::fromName` by replacing invalid dynamic enum case access.

## 1.1.8 - 2026-05-06

### Changed

- Move `$latestResponse` to TestCase class.
- Change from `loadMigrations` to `publishMigrations`.

## 1.1.7 - 2026-03-21

### Added

- Add `addItem()` method to `product()` service - creates a new product listing on Shopee via `POST /api/v2/product/add_item`
- Add `updateItem()` method to `product()` service - updates an existing product listing via `POST /api/v2/product/update_item`
- Add `deleteItem()` method to `product()` service - deletes a product listing via `POST /api/v2/product/delete_item`
- Add `afterAddItemResponse()` hook - automatically creates a `ShopeeProduct` stub and fetches full details via `getItemBaseInfo()` after a successful add
- Add `afterUpdateItemResponse()` hook - updates the local `ShopeeProduct` record with changed fields after a successful update (guards against overwriting fields not included in the request)
- Add `afterDeleteItemResponse()` hook - cascading soft-deletes the `ShopeeProduct` and all related `ShopeeProductModel` records in a DB transaction after a successful delete
- Add `SoftDeletes` support to `ShopeeProduct` and `ShopeeProductModel` models

### Database Changes

- Add `deleted_at` column to `shopee_products` table (soft deletes)
- Add `deleted_at` column to `shopee_product_models` table (soft deletes)

## 1.1.6 - 2026-03-21

### Added

- Add `SHOPEE_REDIRECT_URL` config option to redirect to a user-defined URL after seller authorization completes, instead of rendering the built-in view
- On successful authorization, the redirect URL receives shop and token data as query parameters: `shop_id`, `shop_name`, `region`, `access_token`, `refresh_token`, `expires_at`
- On authorization failure, the redirect URL receives `error=token_failed` and `shop_id`
- Add `SHOPEE_HOME_URL` config option to override the back button destination on the built-in authorization success page (falls back to `APP_URL`)
- Add `ShopServiceTest` unit tests for `generateAuthorizationURL()`
- Add `ShopControllerTest` feature tests for the authorization callback

### Changed

- Redesign built-in authorization success page with a dark luxury aesthetic
- Replace `UnauthorizedException` with `abort(401)` in `ShopController` for correct HTTP response codes
- Wrap `accessToken()` call in `ShopController` with try/catch to handle HTTP errors gracefully

### Security

- Incoming `redirect_url` in the authorization callback is validated against `shopee.redirect_url` config exactly - mismatches are silently discarded to prevent open redirect attacks

## 1.1.5 - 2025-09-20

### Added

- Add `Authorization Flow` section to readme.

## 1.1.4 - 2025-09-11

### Fixed

- Fix refresh token bug `partner_id` need to be an integer.

## 1.1.3 - 2025-09-11

### Changed

- **BREAKING**: Change webpush data structure by using the original webpush data from Shopee and only attach event name to it.

## 1.1.2 - 2025-09-10

### Added

- Add phpunit tests
- Add `ServiceProviderTest` feature test
- Add `ShopeeTest` unit test

### Changed

- Update Github Action workflow to reflect changes

## 1.1.1 - 2025-09-10

### Added

- Add `shopee_webhooks` table
- Add `ShopeeWebhook` model
- Enhanced webhook controller to automatically store all incoming push notifications

### Changed

- Update `ShopeeRequest` model to use `HasUuids` trait
- Improve webhook handling architecture for better data persistence and tracking
- Update README documentation with enhanced webhook integration guide

### Database Changes

- Add `shopee_webhooks` table for storing webhook push data from Shopee

## 1.1.0 - 2025-09-10

### Added

- Add comprehensive `product` service with full inventory management capabilities
  - `getItemList()` - Retrieve paginated list of shop items with filters
  - `getItemBaseInfo()` - Get basic product information including pricing and status
  - `getItemExtraInfo()` - Get extended product details like dimensions and attributes
  - `getModelList()` - Retrieve all variants/models for a specific product
  - `searchItem()` - Search products by name, SKU, or status with pagination
  - `updateStock()` - Update inventory levels for product variants in bulk
- Add database models for product management:
  - `ShopeeProduct` model with `shopee_products` table
  - `ShopeeProductModel` model with `shopee_product_models` table
- Add support for Laravel 9, 10, 11, and 12
- Add static `make()` method on Shopee class for fluent instantiation
- Add `shopId()` method for ad-hoc shop ID setting on requests
- Add comprehensive after-response callback system:
  - `afterGetShopInfoResponse()` for shop info processing
  - `afterGetOrderListResponse()` and `afterGetOrderDetailResponse()` for order processing
  - `afterGetItemListResponse()`, `afterGetItemBaseInfoResponse()`, `afterGetModelListResponse()` for product processing
- Add magic method support in BaseService for dynamic API endpoint mapping
- Add enhanced request logging with `shop_id` tracking in `shopee_requests` table
- Add modern PHP enum support replacing legacy enum classes
- Add enhanced common parameters handling for all API requests
- Add improved webhook support with order status tracking

### Changed

- **BREAKING**: Drop support for Laravel 8
- Update to use modern PHP enum classes instead of legacy enum implementations
- Enhance BaseService architecture with improved method resolution and routing
- Update sandbox URL to new v2 endpoint (`https://openplatform.sandbox.test-stable.shopee.sg`)
- Improve Shopee class initialization to support partner credentials configuration
- Move `generateSignature()` method to main Shopee class for better organization
- Refactor facade namespace structure - move Shopee facade to `Facades` folder
- Update configuration routes to include both legacy and current Shopee API naming conventions
- Enhance URL generation and request handling in BaseService
- Improve migration files structure and data types

### Fixed

- Fix facade namespace issues and wrong namespace references
- Fix authentication bugs in token and refresh token parameter handling
- Fix BaseService bugs related to method resolution and routing
- Fix typos in ProductService implementation
- Fix migration file issues and improve compatibility across Laravel versions
- Fix access token field type - changed to `text` for longer token support
- Fix `request_id` column handling in shopee_requests table

### Database Changes

- Add `shop_id` column to `shopee_requests` table for multi-shop tracking
- Change `access_token` field to `text` type for extended token support
- Add `status` column to `shopee_orders` table
- Change `url` column type in `shopee_requests` table for longer URLs
- Update `request_id` column structure in `shopee_requests` table

## 1.0.0 - 2021-10-27

- Initial release

### Added

- Add `auth` service with `accessToken` and `refreshToken` methods.
- Add `order` service with `list` and `detail` methods.
- Add `payment` service with `escrowDetail` method.
- Add `shop` service with `generateAuthorizationURL` and `getInfo` methods.
- Add `WebhookReceived` event.

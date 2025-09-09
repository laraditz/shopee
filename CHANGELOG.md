# Changelog

All notable changes to `laraditz/shopee` package will be documented in this file

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

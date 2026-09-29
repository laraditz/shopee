<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeReturn extends Model
{
    protected $fillable = [
        'id',
        'shop_id',
        'order_sn',
        'status',
        'negotiation_status',
        'seller_proof_status',
        'seller_compensation_status',
        'refund_amount',
        'currency',
        'return_created_at',
        'return_updated_at',
    ];

    protected $casts = [
        'shop_id' => 'integer',
        'refund_amount' => 'decimal:2',
        'return_created_at' => 'datetime',
        'return_updated_at' => 'datetime',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Upsert a return from a Shopee get_return_list item or get_return_detail response.
     * Only fields present in the payload are written, so existing values are never nulled.
     */
    public static function syncFromPayload(array $data, int|string|null $shopId = null): ?self
    {
        $returnSn = data_get($data, 'return_sn');

        if (blank($returnSn)) {
            return null;
        }

        $attributes = [
            'shop_id' => $shopId,
            'order_sn' => data_get($data, 'order_sn'),
            'status' => data_get($data, 'status'),
            'negotiation_status' => data_get($data, 'negotiation.negotiation_status', data_get($data, 'negotiation_status')),
            'seller_proof_status' => data_get($data, 'seller_proof.seller_proof_status', data_get($data, 'seller_proof_status')),
            'seller_compensation_status' => data_get($data, 'seller_compensation.seller_compensation_status', data_get($data, 'seller_compensation_status')),
            'refund_amount' => data_get($data, 'refund_amount'),
            'currency' => data_get($data, 'currency'),
            'return_created_at' => static::toDateTime(data_get($data, 'create_time')),
            'return_updated_at' => static::toDateTime(data_get($data, 'update_time')),
        ];

        return static::updateOrCreate(
            ['id' => (string) $returnSn],
            array_filter($attributes, fn($value) => !is_null($value))
        );
    }

    private static function toDateTime(mixed $timestamp): ?Carbon
    {
        return is_numeric($timestamp) && $timestamp > 0
            ? Carbon::createFromTimestamp((int) $timestamp)
            : null;
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopeeShop::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopeeOrder::class, 'order_sn');
    }
}

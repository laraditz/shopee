<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ShopeeRequest extends Model
{
    use MassPrunable, HasFactory, HasUuids;

    protected $fillable = ['id', 'shop_id', 'action', 'url', 'request_id', 'request', 'response', 'error'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'request' => 'json',
        'response' => 'json',
    ];

    public function prunable(): Builder
    {
        $days = config('shopee.logging.prune_after_days');

        if (is_numeric($days) && (int) $days > 0) {
            return static::where('created_at', '<=', now()->subDays((int) $days));
        }

        return static::query()->whereRaw('1 = 0');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopeeShop::class);
    }
}

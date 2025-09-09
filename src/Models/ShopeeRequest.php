<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopeeRequest extends Model
{
    use HasFactory;

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

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->id ?? (string) Str::orderedUuid();
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopeeShop::class);
    }
}

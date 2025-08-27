<?php

namespace Laraditz\Shopee\Models;

use Laraditz\Shopee\Enums\ShopStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopeeShop extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = ['id', 'name', 'region', 'status', 'code'];

    protected $casts = [
        'id' => 'integer',
        'status' => ShopStatus::class,
    ];

    public function accessToken()
    {
        return $this->morphOne(ShopeeAccessToken::class, 'entity');
    }
}

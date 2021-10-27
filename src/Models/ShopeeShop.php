<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeShop extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = ['id', 'name', 'region', 'status', 'code'];

    protected $casts = [
        'id' => 'integer',
    ];

    public function accessToken()
    {
        return $this->morphOne(ShopeeAccessToken::class, 'entity');
    }
}

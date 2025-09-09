<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeAccessToken extends Model
{
    use HasFactory;

    protected $fillable = ['entity_type', 'entity_id', 'access_token', 'refresh_token', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}

<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeOrder extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'shop_id'];

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function shop()
    {
        return $this->belongsTo(ShopeeShop::class);
    }
}

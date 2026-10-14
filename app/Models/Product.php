<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'category',
        'cost_price',
        'sell_price',
        'stock',
        'expired_date',
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];
}
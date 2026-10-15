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

    // Sisa hari sampai expired: positif = masih ada waktu, 0 = hari ini,
    // negatif = sudah lewat. Null kalau produk tidak punya tanggal expired.
    // Dipanggil di view sebagai $product->days_left
    public function getDaysLeftAttribute(): ?int
    {
        if (! $this->expired_date) {
            return null;
        }

        return (int) today()->diffInDays($this->expired_date, false);
    }
}
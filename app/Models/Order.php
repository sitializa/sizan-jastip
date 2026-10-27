<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    // SoftDeletes: delete() hanya mengisi deleted_at, barisnya tetap ada (jadi arsip).
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'order_date',
        'payment_status',
        'tracking_number',
        'shipping_status',
        'delete_reason',
        'restored_at',
    ];

    protected $casts = [
        'order_date'  => 'date',
        'restored_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
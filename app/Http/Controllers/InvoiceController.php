<?php

namespace App\Http\Controllers;

use App\Models\Order;

class InvoiceController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['customer', 'items.product']);

        $total = $order->items->sum(fn ($item) => $item->qty * $item->price);

        return view('invoices.show', compact('order', 'total'));
    }
}
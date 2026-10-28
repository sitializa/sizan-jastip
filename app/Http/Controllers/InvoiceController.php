<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // Versi 1: invoice satu order.
    public function show(Order $order)
    {
        $order->load(['customer', 'items.product']);

        $total = $order->items->sum(fn ($item) => $item->qty * $item->price);

        return view('invoices.show', compact('order', 'total'));
    }

    // Versi 2: rekap semua order milik satu customer.
    public function customer(Request $request, Customer $customer)
    {
        // "belum" = hanya order yang belum dibayar. Selain itu = semua order.
        $status = $request->query('status') === 'belum' ? 'belum' : 'semua';

        // Order yang sudah diarsipkan tidak ikut (otomatis lewat SoftDeletes).
        $orders = $customer->orders()
            ->with('items.product')
            ->when($status === 'belum', fn ($q) => $q->where('payment_status', 'belum'))
            ->orderBy('order_date')
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            $order->total = $order->items->sum(fn ($i) => $i->qty * $i->price);
        }

        $total      = $orders->sum('total');
        $sudahBayar = $orders->where('payment_status', 'sudah')->sum('total');

        $pcsDikirim = $orders->where('shipping_status', 'sudah')
            ->sum(fn ($o) => $o->items->sum('qty'));
        $totalPcs   = $orders->sum(fn ($o) => $o->items->sum('qty'));

        return view('invoices.customer', [
            'customer'      => $customer,
            'orders'        => $orders,
            'status'        => $status,
            'total'         => $total,
            'sudahBayar'    => $sudahBayar,
            'belumBayar'    => $total - $sudahBayar,
            'totalPcs'      => $totalPcs,
            'pcsDikirim'    => $pcsDikirim,
            'pcsBelumKirim' => $totalPcs - $pcsDikirim,
        ]);
    }
}
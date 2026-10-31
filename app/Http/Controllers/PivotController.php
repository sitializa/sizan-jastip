<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PivotController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        // Order yang sudah diarsipkan tidak ikut (otomatis lewat SoftDeletes).
        $orders = Order::with(['customer', 'items.product'])
            // Cocok di nama/HP customer ATAU nama produk. Dibungkus satu kelompok.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    })->orWhereHas('items.product', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('order_date')
            ->orderBy('id')
            ->get();

        // Total rupiah dan jumlah pcs dihitung sekali per order.
        foreach ($orders as $order) {
            $order->total = $order->items->sum(fn ($i) => $i->qty * $i->price);
            $order->pcs   = $order->items->sum('qty');
        }

        // 1. Matriks bayar x kirim
        $matrix = [];
        foreach (['sudah', 'belum'] as $bayar) {
            foreach (['sudah', 'belum'] as $kirim) {
                $pilih = $orders->where('payment_status', $bayar)->where('shipping_status', $kirim);
                $matrix[$bayar][$kirim] = (object) [
                    'order' => $pilih->count(),
                    'total' => $pilih->sum('total'),
                ];
            }
        }

        // 2. Empat daftar customer
        $sections = [
            [
                'id'      => 'dikirim',
                'title'   => 'Sudah dikirim',
                'groups'  => $this->groupByCustomer($orders->where('shipping_status', 'sudah')),
                'tagihan' => false,
            ],
            [
                'id'      => 'belum-dikirim',
                'title'   => 'Belum dikirim',
                'groups'  => $this->groupByCustomer($orders->where('shipping_status', 'belum')),
                'tagihan' => false,
            ],
            [
                'id'      => 'dibayar',
                'title'   => 'Sudah dibayar',
                'groups'  => $this->groupByCustomer($orders->where('payment_status', 'sudah')),
                'tagihan' => false,
            ],
            [
                'id'      => 'belum-dibayar',
                'title'   => 'Belum dibayar',
                'groups'  => $this->groupByCustomer($orders->where('payment_status', 'belum')),
                'tagihan' => true,
            ],
        ];

        // 3. Ringkasan per customer
        $perCustomer = $orders->groupBy('customer_id')->map(function ($group) {
            $customer = $group->first()->customer;

            return (object) [
                'name'            => $customer->name ?? $customer->phone ?? '-',
                'jumlah_order'    => $group->count(),
                'pcs'             => $group->sum('pcs'),
                'total'           => $group->sum('total'),
                'dibayar'         => $group->where('payment_status', 'sudah')->sum('total'),
                'belum_bayar'     => $group->where('payment_status', 'belum')->sum('total'),
                'pcs_dikirim'     => $group->where('shipping_status', 'sudah')->sum('pcs'),
                'pcs_belum_kirim' => $group->where('shipping_status', 'belum')->sum('pcs'),
            ];
        })->sortBy('name')->values();

        // 4. Produk dipesan oleh siapa saja
        $perProduct = [];
        foreach ($orders as $order) {
            $customer = $order->customer;

            foreach ($order->items as $item) {
                $perProduct[$item->product_id] ??= (object) [
                    'name'   => $item->product->name,
                    'brand'  => $item->product->brand,
                    'pcs'    => 0,
                    'buyers' => [],
                ];

                $perProduct[$item->product_id]->pcs += $item->qty;
                $perProduct[$item->product_id]->buyers[] = (object) [
                    'name'    => $customer->name ?? $customer->phone ?? '-',
                    'qty'     => $item->qty,
                    'order'   => $order,
                    'paid'    => $order->payment_status === 'sudah',
                    'shipped' => $order->shipping_status === 'sudah',
                ];
            }
        }
        $perProduct = collect($perProduct)->sortBy('name')->values();

        return view('pivot.index', [
            'search'      => $search,
            'matrix'      => $matrix,
            'sections'    => $sections,
            'perCustomer' => $perCustomer,
            'perProduct'  => $perProduct,
            'totalOrder'  => $orders->count(),
        ]);
    }

    // Kelompokkan order per customer, lengkap dengan jumlah pcs dan total rupiah.
    private function groupByCustomer(Collection $orders): Collection
    {
        return $orders->groupBy('customer_id')->map(function ($group) {
            $customer = $group->first()->customer;

            return (object) [
                'id'     => $customer->id,
                'name'   => $customer->name ?? $customer->phone ?? '-',
                'phone'  => $customer->phone,
                'orders' => $group,
                'pcs'    => $group->sum('pcs'),
                'total'  => $group->sum('total'),
            ];
        })->sortBy('name')->values();
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function profit()
    {
        // Satu baris hasil = satu bulan. Dihitung dari order_items yang
        // order-nya sudah dibayar, memakai harga dan modal "foto" saat order.
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'sudah')
            ->whereNull('orders.deleted_at')
            ->selectRaw("
                DATE_FORMAT(orders.order_date, '%Y-%m') as bulan,
                COUNT(DISTINCT orders.id) as jumlah_order,
                SUM(order_items.qty) as jumlah_pcs,
                SUM(order_items.qty * order_items.price) as omzet,
                SUM(order_items.qty * order_items.cost_price) as modal,
                SUM(order_items.qty * (order_items.price - order_items.cost_price)) as untung
            ")
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->get()
            ->map(function ($row) {
                // "2026-10" diubah jadi tanggal supaya bisa tampil sebagai "Oktober 2026".
                $row->nama_bulan = Carbon::createFromFormat('Y-m-d', $row->bulan . '-01')
                    ->translatedFormat('F Y');

                return $row;
            });

        return view('reports.profit', [
            'rows'        => $rows,
            'totalOmzet'  => $rows->sum('omzet'),
            'totalModal'  => $rows->sum('modal'),
            'totalUntung' => $rows->sum('untung'),
        ]);
    }
}
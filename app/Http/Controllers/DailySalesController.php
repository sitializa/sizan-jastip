<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailySalesController extends Controller
{
    public function index(Request $request)
    {
        // Bulan yang ditampilkan, format "2026-10". Kalau tidak valid, pakai bulan ini.
        $bulan = (string) $request->query('bulan', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }

        $awal  = Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();

        // Satu baris hasil = satu hari. Semua order dihitung, sudah dibayar atau belum.
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.order_date', [$awal->toDateString(), $akhir->toDateString()])
            ->selectRaw("
                orders.order_date as tanggal,
                COUNT(DISTINCT orders.id) as jumlah_order,
                SUM(order_items.qty) as jumlah_pcs,
                SUM(order_items.qty * order_items.price) as total,
                SUM(CASE WHEN orders.payment_status = 'sudah'
                         THEN order_items.qty * order_items.price ELSE 0 END) as sudah_bayar
            ")
            ->groupBy('orders.order_date')
            ->orderByDesc('orders.order_date')
            ->get()
            ->map(function ($row) {
                $row->label       = Carbon::parse($row->tanggal)->translatedFormat('l, d F Y');
                $row->belum_bayar = $row->total - $row->sudah_bayar;

                return $row;
            });

        // Ringkasan khusus hari ini (tidak tergantung bulan yang dipilih).
        $hariIni = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.order_date', today())
            ->selectRaw("
                COUNT(DISTINCT orders.id) as jumlah_order,
                COALESCE(SUM(order_items.qty), 0) as jumlah_pcs,
                COALESCE(SUM(order_items.qty * order_items.price), 0) as total
            ")
            ->first();

        return view('sales.daily', [
            'rows'        => $rows,
            'bulan'       => $bulan,
            'namaBulan'   => $awal->translatedFormat('F Y'),
            'hariIni'     => $hariIni,
            'totalOrder'  => $rows->sum('jumlah_order'),
            'totalPcs'    => $rows->sum('jumlah_pcs'),
            'totalRupiah' => $rows->sum('total'),
            'totalBayar'  => $rows->sum('sudah_bayar'),
            'totalBelum'  => $rows->sum('belum_bayar'),
        ]);
    }
}
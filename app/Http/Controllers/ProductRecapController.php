<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductRecapController extends Controller
{
    public function index(Request $request)
    {
        $search   = trim((string) $request->query('q', ''));
        $category = $request->query('kategori');

        // Hanya kategori yang terdaftar yang dipakai, nilai ngawur dari alamat diabaikan.
        if (! in_array($category, ProductController::CATEGORIES, true)) {
            $category = null;
        }

        // Jumlah terjual per produk dari semua order (sudah dibayar atau belum),
        // sekaligus dipecah: berapa yang sudah dibayar. Order di Arsip tidak dihitung.
        $sold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.qty) as terjual'),
                DB::raw('SUM(order_items.qty * order_items.price) as omzet'),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'sudah' THEN order_items.qty ELSE 0 END) as dibayar"),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'sudah' THEN order_items.qty * order_items.price ELSE 0 END) as omzet_dibayar")
            )
            ->groupBy('order_items.product_id');

        // leftJoin: produk yang belum pernah terjual tetap ikut, dengan angka 0.
        $products = Product::query()
            ->leftJoinSub($sold, 's', 's.product_id', '=', 'products.id')
            ->select(
                'products.*',
                DB::raw('COALESCE(s.terjual, 0) as terjual'),
                DB::raw('COALESCE(s.omzet, 0) as omzet'),
                DB::raw('COALESCE(s.dibayar, 0) as dibayar'),
                DB::raw('COALESCE(s.omzet_dibayar, 0) as omzet_dibayar')
            )
            // Cari nama ATAU brand, dibungkus satu kelompok supaya filter kategori tetap mengikat.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                      ->orWhere('products.brand', 'like', "%{$search}%");
                });
            })
            ->when($category, fn ($query) => $query->where('products.category', $category))
            ->orderBy('products.name')
            ->get();

        $sudahTerjual = $products->where('terjual', '>', 0)->sortByDesc('terjual')->values();
        $sudahDibayar = $products->where('dibayar', '>', 0)->sortByDesc('dibayar')->values();
        $belumTerjual = $products->where('terjual', '==', 0)->values();

        $totalTerjual = (int) $sudahTerjual->sum('terjual');
        $totalDibayar = (int) $sudahDibayar->sum('dibayar');
        $totalSisa    = (int) $products->sum('stock');
        $totalMasuk   = $totalTerjual + $totalSisa;

        return view('recap.products', [
            'search'            => $search,
            'category'          => $category,
            'categories'        => ProductController::CATEGORIES,
            'sudahTerjual'      => $sudahTerjual,
            'sudahDibayar'      => $sudahDibayar,
            'belumTerjual'      => $belumTerjual,
            'jumlahProduk'      => $products->count(),
            'totalTerjual'      => $totalTerjual,
            'totalDibayar'      => $totalDibayar,
            'totalBelumDibayar' => $totalTerjual - $totalDibayar,
            'totalSisa'         => $totalSisa,
            'totalMasuk'        => $totalMasuk,
            'persenLaku'        => $totalMasuk > 0 ? round($totalTerjual / $totalMasuk * 100) : 0,
            'totalOmzet'        => (int) $sudahTerjual->sum('omzet'),
            'totalOmzetDibayar' => (int) $sudahDibayar->sum('omzet_dibayar'),
            'nilaiStok'         => (int) $products->sum(fn ($p) => $p->stock * $p->cost_price),
        ]);
    }
}
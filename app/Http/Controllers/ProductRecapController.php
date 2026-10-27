<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRecapController extends Controller
{
    public function index()
    {
        // Jumlah terjual dan omzet per produk, dihitung dari semua order_items.
        $sold = DB::table('order_items')
            ->select(
                'product_id',
                DB::raw('SUM(qty) as terjual'),
                DB::raw('SUM(qty * price) as omzet')
            )
            ->groupBy('product_id');

        // leftJoin: produk yang belum pernah terjual tetap ikut, dengan terjual = 0.
        $products = Product::query()
            ->leftJoinSub($sold, 's', 's.product_id', '=', 'products.id')
            ->select(
                'products.*',
                DB::raw('COALESCE(s.terjual, 0) as terjual'),
                DB::raw('COALESCE(s.omzet, 0) as omzet')
            )
            ->orderBy('products.name')
            ->get();

        $sudahTerjual = $products->where('terjual', '>', 0)->sortByDesc('terjual')->values();
        $belumTerjual = $products->where('terjual', '==', 0)->values();

        $totalTerjual = (int) $sudahTerjual->sum('terjual');
        $totalSisa    = (int) $products->sum('stock');
        $totalMasuk   = $totalTerjual + $totalSisa;

        return view('recap.products', [
            'sudahTerjual' => $sudahTerjual,
            'belumTerjual' => $belumTerjual,
            'jumlahProduk' => $products->count(),
            'totalTerjual' => $totalTerjual,
            'totalSisa'    => $totalSisa,
            'totalMasuk'   => $totalMasuk,
            'persenLaku'   => $totalMasuk > 0 ? round($totalTerjual / $totalMasuk * 100) : 0,
            'totalOmzet'   => (int) $sudahTerjual->sum('omzet'),
            'nilaiStok'    => (int) $products->sum(fn ($p) => $p->stock * $p->cost_price),
        ]);
    }
}
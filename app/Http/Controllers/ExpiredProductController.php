<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ExpiredProductController extends Controller
{
    public function __invoke(Request $request)
    {
        // Rentang hari ke depan, dibatasi 1-365 supaya angka aneh dari URL tidak bikin masalah.
        $days = max(1, min((int) $request->query('hari', 90), 365));

        $products = Product::whereNotNull('expired_date')
            ->where('stock', '>', 0)
            ->whereDate('expired_date', '<=', today()->addDays($days))
            ->orderBy('expired_date') // paling mepet (atau sudah lewat) di paling atas
            ->get();

        return view('expired.index', compact('products', 'days'));
    }
}
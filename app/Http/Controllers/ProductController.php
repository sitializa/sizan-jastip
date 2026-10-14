<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // Daftar kategori yang diizinkan. Mau tambah kategori? Cukup tambah di sini.
    public const CATEGORIES = ['makeup', 'skincare', 'baju', 'celana', 'mainan'];

    public function index()
    {
        $products = Product::latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create', [
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request));

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // Aturan validasi dipakai bersama oleh store dan update.
    private function validated(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'brand'        => ['nullable', 'string', 'max:255'],
            'category'     => ['required', Rule::in(self::CATEGORIES)],
            'cost_price'   => ['required', 'integer', 'min:0'],
            'sell_price'   => ['required', 'integer', 'min:0'],
            'stock'        => ['required', 'integer', 'min:0'],
            'expired_date' => ['nullable', 'date'],
        ]);
    }
}
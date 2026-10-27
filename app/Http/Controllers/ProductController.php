<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // Daftar kategori yang diizinkan. Mau tambah kategori? Cukup tambah di sini.
    public const CATEGORIES = ['makeup', 'skincare', 'baju', 'celana', 'mainan', 'non-makeup'];

    public function index(Request $request)
    {
        $search    = trim((string) $request->query('q', ''));
        $category  = $request->query('kategori');
        $onlyCheck = $request->boolean('cek');

        // Filter stok: "ada" = masih ada sisa, "habis" = stok 0. Selain itu diabaikan.
        $stock = $request->query('stok');
        if (! in_array($stock, ['ada', 'habis'], true)) {
            $stock = null;
        }

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($onlyCheck, fn ($query) => $query->where('needs_check', true))
            ->when($stock === 'ada', fn ($query) => $query->where('stock', '>', 0))
            ->when($stock === 'habis', fn ($query) => $query->where('stock', '<=', 0))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products'   => $products,
            'search'     => $search,
            'category'   => $category,
            'onlyCheck'  => $onlyCheck,
            'stock'      => $stock,
            'categories' => self::CATEGORIES,
        ]);
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
        // Produk yang sudah pernah dijual atau dibeli tidak boleh dihapus,
        // supaya rincian order, invoice, dan laporan lama tidak rusak.
        $dipakai = OrderItem::where('product_id', $product->id)->exists()
            || PurchaseItem::where('product_id', $product->id)->exists();

        if ($dipakai) {
            return redirect()->route('products.index')
                ->with('error', "Produk \"{$product->name}\" tidak bisa dihapus karena sudah dipakai di order atau pembelian. Kalau sudah tidak dijual, kosongkan stoknya saja.");
        }

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
            'needs_check'  => ['nullable', 'boolean'],
        ]);
    }
}
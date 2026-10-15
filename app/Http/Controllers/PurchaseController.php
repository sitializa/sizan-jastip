<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'items'])->latest()->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers'  => Supplier::orderBy('name')->get(),
            'products'   => Product::orderBy('name')->get(),
            'categories' => ProductController::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'   => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'extra_cost'    => ['nullable', 'integer', 'min:0'],
            'items'         => ['required', 'array', 'min:1'],

            // product_id berisi id produk lama, atau kata "new" untuk produk baru.
            'items.*.product_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'new' && ! Product::whereKey($value)->exists()) {
                        $fail('Ada produk yang dipilih tidak valid.');
                    }
                },
            ],
            'items.*.qty'   => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'integer', 'min:1'],

            // Data produk baru: wajib hanya kalau baris itu memilih "+ Produk baru...".
            'items.*.new_name'         => ['required_if:items.*.product_id,new', 'nullable', 'string', 'max:255'],
            'items.*.new_brand'        => ['nullable', 'string', 'max:255'],
            'items.*.new_category'     => ['required_if:items.*.product_id,new', 'nullable', Rule::in(ProductController::CATEGORIES)],
            'items.*.new_expired_date' => ['nullable', 'date'],
        ], [], [
            'items.*.product_id'   => 'produk',
            'items.*.qty'          => 'qty',
            'items.*.price'        => 'harga beli',
            'items.*.new_name'     => 'nama produk baru',
            'items.*.new_category' => 'kategori produk baru',
        ]);

        // Kolom dikosongkan = 0.
        $extraCost = (int) ($data['extra_cost'] ?? 0);

        // Jatah biaya tambahan per pcs = biaya tambahan / total pcs yang dibeli.
        $totalQty = collect($data['items'])->sum('qty');
        $sharePerPcs = $extraCost / $totalQty;

        // Satu paket: pembelian, produk baru, item, dan stok berhasil semua atau batal semua.
        $purchase = DB::transaction(function () use ($data, $extraCost, $sharePerPcs) {
            $purchase = Purchase::create([
                'supplier_id'   => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'extra_cost'    => $extraCost,
            ]);

            foreach ($data['items'] as $row) {
                // harga modal bersih per pcs = harga beli + jatah biaya tambahan
                $netPrice = round($row['price'] + $sharePerPcs, 2);

                if ($row['product_id'] === 'new') {
                    // Cegah produk kembar: nama + brand yang sama sudah ada.
                    $alreadyExists = Product::where('name', $row['new_name'])
                        ->where('brand', $row['new_brand'] ?? null)
                        ->exists();

                    if ($alreadyExists) {
                        throw ValidationException::withMessages([
                            'items' => "Produk \"{$row['new_name']}\" sudah ada di daftar. Pilih dari dropdown, bukan sebagai produk baru.",
                        ]);
                    }

                    // Produk baru langsung masuk ke tabel products dengan stok = qty yang dibeli.
                    // sell_price diisi 0 dulu karena kolomnya wajib ada; harga jual yang
                    // sebenarnya diisi nanti lewat halaman Produk.
                    $product = Product::create([
                        'name'         => $row['new_name'],
                        'brand'        => $row['new_brand'] ?? null,
                        'category'     => $row['new_category'],
                        'cost_price'   => (int) round($netPrice),
                        'sell_price'   => 0,
                        'stock'        => $row['qty'],
                        'expired_date' => $row['new_expired_date'] ?? null,
                    ]);

                    $productId = $product->id;
                } else {
                    $productId = $row['product_id'];

                    // Produk lama: stok = stok + qty, dikerjakan langsung di database.
                    Product::whereKey($productId)->increment('stock', $row['qty']);
                }

                $purchase->items()->create([
                    'product_id' => $productId,
                    'qty'        => $row['qty'],
                    'price'      => $row['price'],
                    'net_price'  => $netPrice,
                ]);
            }

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian dicatat dan stok sudah bertambah.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }
}
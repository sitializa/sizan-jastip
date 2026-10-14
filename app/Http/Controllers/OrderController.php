<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'items'])->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        return view('orders.create', [
            'customers' => Customer::orderBy('phone')->get(),
            'products'  => Product::where('stock', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'        => ['required', 'exists:customers,id'],
            'order_date'         => ['required', 'date'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
        ]);

        // Kalau produk yang sama dipilih di dua baris, jumlahnya digabung.
        $wanted = collect($data['items'])
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->sum('qty'));

        // Semua langkah di dalam satu paket: berhasil semua atau batal semua.
        $order = DB::transaction(function () use ($data, $wanted) {
            // lockForUpdate: produk "dikunci" supaya dua order bersamaan
            // tidak bisa sama-sama mengambil stok terakhir.
            $products = Product::whereIn('id', $wanted->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 1. Cek stok semua produk dulu
            foreach ($wanted as $productId => $qty) {
                $product = $products[$productId];

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup (sisa {$product->stock}, diminta {$qty}).",
                    ]);
                }
            }

            // 2. Simpan order
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'order_date'  => $data['order_date'],
            ]);

            // 3. Simpan item (harga disalin dari server) dan kurangi stok
            foreach ($wanted as $productId => $qty) {
                $product = $products[$productId];

                $order->items()->create([
                    'product_id' => $product->id,
                    'qty'        => $qty,
                    'price'      => $product->sell_price,
                ]);

                $product->decrement('stock', $qty);
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order berhasil dibuat.');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.product']);

        return view('orders.show', compact('order'));
    }
}
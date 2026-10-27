<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $orders = Order::with(['customer', 'items'])
            // Nama ATAU nomor HP customer, dibungkus satu kelompok.
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('orders.create', [
            'customers' => Customer::orderBy('name')->orderBy('phone')->get(),
            // Hanya produk yang stoknya ada dan harga jualnya sudah diisi.
            'products'  => Product::where('stock', '>', 0)->where('sell_price', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // customer_id berisi id customer lama, atau kata "new" untuk customer baru.
            'customer_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'new' && ! Customer::whereKey($value)->exists()) {
                        $fail('Customer yang dipilih tidak valid.');
                    }
                },
            ],
            'new_customer_name'  => ['required_if:customer_id,new', 'nullable', 'string', 'max:255'],
            'new_customer_phone' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'phone')],

            'order_date'         => ['required', 'date'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
        ], [], [
            'new_customer_name'  => 'nama customer baru',
            'new_customer_phone' => 'no. HP customer baru',
        ]);

        // Kalau produk yang sama dipilih di dua baris, jumlahnya digabung.
        $wanted = collect($data['items'])
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->sum('qty'));

        // Semua langkah di dalam satu paket: berhasil semua atau batal semua.
        $order = DB::transaction(function () use ($data, $wanted) {
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

            // 2. Customer: pakai yang dipilih, atau buat baru dari nama.
            $customerId = $data['customer_id'] === 'new'
                ? Customer::create([
                    'name'  => $data['new_customer_name'],
                    'phone' => $data['new_customer_phone'] ?? null,
                ])->id
                : $data['customer_id'];

            // 3. Simpan order
            $order = Order::create([
                'customer_id' => $customerId,
                'order_date'  => $data['order_date'],
            ]);

            // 4. Simpan item dan kurangi stok.
            //    price dan cost_price disalin dari server (foto saat order dibuat).
            foreach ($wanted as $productId => $qty) {
                $product = $products[$productId];

                $order->items()->create([
                    'product_id' => $product->id,
                    'qty'        => $qty,
                    'price'      => $product->sell_price,
                    'cost_price' => $product->cost_price,
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

    public function destroy(Order $order)
    {
        // Barang yang sudah dikirim tidak bisa "dikembalikan" ke stok.
        if ($order->shipping_status === 'sudah') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order yang sudah dikirim tidak bisa dihapus. Kalau memang salah catat, hilangkan centang "Sudah dikirim" dulu.');
        }

        $id = $order->id;

        // Satu paket: stok kembali dan order terhapus berhasil semua atau batal semua.
        DB::transaction(function () use ($order) {
            $items = $order->items()->get();

            $products = Product::whereIn('id', $items->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $products[$item->product_id]->increment('stock', $item->qty);
            }

            // order_items ikut terhapus otomatis (cascadeOnDelete di migration).
            $order->delete();
        });

        return redirect()->route('orders.index')
            ->with('success', "Order #{$id} dihapus dan stok produk dikembalikan.");
    }

    public function updateTracking(Request $request, Order $order)
    {
        $data = $request->validate([
            'tracking_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('orders', 'tracking_number')->ignore($order->id),
            ],
        ]);

        $order->update(['tracking_number' => $data['tracking_number']]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Nomor resi disimpan.');
    }

    public function updateShipping(Request $request, Order $order)
    {
        $shipped = $request->boolean('shipped');

        $order->update([
            'shipping_status' => $shipped ? 'sudah' : 'belum',
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Status pengiriman diperbarui.');
    }

    public function updatePayment(Request $request, Order $order)
    {
        $paid = $request->boolean('paid');

        $order->update([
            'payment_status' => $paid ? 'sudah' : 'belum',
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Status pembayaran diperbarui.');
    }
}
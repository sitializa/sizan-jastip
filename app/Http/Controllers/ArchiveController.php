<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ArchiveController extends Controller
{
    public function index()
    {
        $orders = Order::onlyTrashed()
            ->with(['customer', 'items.product'])
            ->orderByDesc('deleted_at')
            ->paginate(15);

        return view('archive.index', compact('orders'));
    }

    public function restore($id)
    {
        $order = Order::onlyTrashed()->with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($order) {
                $products = Product::whereIn('id', $order->items->pluck('product_id'))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Stok semua produk dicek dulu. Kalau ada yang kurang, tidak ada yang diubah.
                foreach ($order->items as $item) {
                    $product = $products[$item->product_id];

                    if ($product->stock < $item->qty) {
                        throw new RuntimeException(
                            "Stok {$product->name} tidak cukup untuk memulihkan order ini (sisa {$product->stock}, dibutuhkan {$item->qty})."
                        );
                    }
                }

                foreach ($order->items as $item) {
                    $products[$item->product_id]->decrement('stock', $item->qty);
                }

                $order->restore();
                $order->update(['delete_reason' => null, 'restored_at' => now()]);
            });
        } catch (RuntimeException $e) {
            return redirect()->route('archive.index')->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order dipulihkan dan stok dikurangi lagi. Hitungan 24 jam mulai dari sekarang, jadi tinggal centang "Sudah dibayar".');
    }

    public function destroy($id)
    {
        // Hapus permanen. Stok tidak diubah karena sudah dikembalikan saat diarsipkan.
        // order_items ikut terhapus otomatis (cascade di database).
        Order::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('archive.index')
            ->with('success', 'Order dihapus permanen.');
    }
}
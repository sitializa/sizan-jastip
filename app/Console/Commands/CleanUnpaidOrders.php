<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUnpaidOrders extends Command
{
    protected $signature = 'orders:clean-unpaid';

    protected $description = 'Hapus order yang belum dibayar lebih dari 24 jam dan kembalikan stoknya';

    public function handle(): int
    {
        $expired = Order::with('items')
            ->where('payment_status', 'belum')
            ->where('shipping_status', 'belum')
            ->where('created_at', '<=', now()->subHours(24))
            ->get();

        foreach ($expired as $order) {
            // Satu order = satu paket: stok balik dan order terhapus, atau batal dua-duanya.
            DB::transaction(function () use ($order) {
                // 1. Kembalikan stok dulu (item masih ada).
                foreach ($order->items as $item) {
                    Product::whereKey($item->product_id)->increment('stock', $item->qty);
                }

                // 2. Baru hapus order (order_items ikut terhapus lewat cascade).
                $order->delete();
            });
        }

        $this->info("{$expired->count()} order belum dibayar dihapus dan stoknya dikembalikan.");

        return self::SUCCESS;
    }
}
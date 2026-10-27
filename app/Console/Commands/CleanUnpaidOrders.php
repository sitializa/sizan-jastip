<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUnpaidOrders extends Command
{
    protected $signature = 'orders:clean-unpaid';

    protected $description = 'Arsipkan order yang belum dibayar dan belum dikirim lebih dari 24 jam, lalu kembalikan stoknya';

    public function handle(): int
    {
        $batas = now()->subHours(24);

        // Umur dihitung dari kapan terakhir dipulihkan, atau dari dibuat kalau belum pernah dipulihkan.
        $orders = Order::query()
            ->where('payment_status', 'belum')
            ->where('shipping_status', 'belum')
            ->where(function ($q) use ($batas) {
                $q->where('restored_at', '<', $batas)
                  ->orWhere(function ($q2) use ($batas) {
                      $q2->whereNull('restored_at')->where('created_at', '<', $batas);
                  });
            })
            ->get();

        $jumlah = 0;

        foreach ($orders as $order) {
            DB::transaction(function () use ($order, &$jumlah) {
                // Baca ulang dengan kunci: bisa jadi order baru saja dibayar.
                $fresh = Order::lockForUpdate()->find($order->id);

                if (! $fresh || $fresh->payment_status !== 'belum' || $fresh->shipping_status !== 'belum') {
                    return;
                }

                $items = $fresh->items()->get();

                $products = Product::whereIn('id', $items->pluck('product_id'))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $products[$item->product_id]->increment('stock', $item->qty);
                }

                $fresh->update(['delete_reason' => 'otomatis']);
                $fresh->delete(); // soft delete = pindah ke Arsip

                $jumlah++;
            });
        }

        $this->info("{$jumlah} order diarsipkan dan stoknya dikembalikan.");

        return self::SUCCESS;
    }
}
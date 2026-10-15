<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom. Default 0 supaya baris lama tidak error saat kolom dibuat.
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('cost_price')->default(0)->after('price');
        });

        // 2. Backfill: isi order lama dengan modal produk saat ini,
        //    supaya tidak dibiarkan 0.
        DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->update(['order_items.cost_price' => DB::raw('products.cost_price')]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
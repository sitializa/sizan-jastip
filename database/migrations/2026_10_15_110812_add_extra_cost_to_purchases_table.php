<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedInteger('extra_cost')->default(0)->after('purchase_date');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            // harga modal bersih per pcs = harga beli + jatah biaya tambahan
            $table->decimal('net_price', 12, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('net_price');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('extra_cost');
        });
    }
};
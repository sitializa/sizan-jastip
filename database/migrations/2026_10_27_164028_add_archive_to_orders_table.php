<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes();                        // deleted_at: terisi = order ada di Arsip
            $table->string('delete_reason')->nullable();  // 'otomatis' atau 'manual'
            $table->timestamp('restored_at')->nullable(); // kapan terakhir dipulihkan
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['deleted_at', 'delete_reason', 'restored_at']);
        });
    }
};
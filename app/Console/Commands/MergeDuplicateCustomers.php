<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicateCustomers extends Command
{
    protected $signature = 'customers:merge-duplicates';

    protected $description = 'Gabungkan customer tanpa nomor HP yang namanya sama';

    public function handle(): int
    {
        // Hanya customer TANPA nomor HP yang dikelompokkan menurut nama (huruf besar/kecil diabaikan).
        $groups = Customer::whereNull('phone')
            ->whereNotNull('name')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($c) => mb_strtolower(trim($c->name)))
            ->filter(fn ($g) => $g->count() > 1);

        if ($groups->isEmpty()) {
            $this->info('Tidak ada customer kembar.');

            return self::SUCCESS;
        }

        // Tampilkan dulu apa yang akan digabung.
        foreach ($groups as $group) {
            $keep   = $group->first();
            $others = $group->skip(1)->pluck('id')->implode(', #');
            $this->line("\"{$keep->name}\": pertahankan #{$keep->id}, gabungkan #{$others}");
        }

        if (! $this->confirm('Gabungkan semuanya? Order dipindah ke customer yang dipertahankan.')) {
            $this->warn('Dibatalkan, tidak ada yang diubah.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($groups) {
            foreach ($groups as $group) {
                $keep   = $group->first();
                $others = $group->skip(1)->pluck('id');

                // Pindahkan semua order (termasuk yang ada di Arsip), baru hapus customer kembarnya.
                DB::table('orders')->whereIn('customer_id', $others)->update(['customer_id' => $keep->id]);
                Customer::whereIn('id', $others)->delete();
            }
        });

        $this->info('Selesai. Customer kembar sudah digabung.');

        return self::SUCCESS;
    }
}
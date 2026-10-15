<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hapus order belum dibayar dan belum dikirim yang lebih dari 24 jam,
// lalu kembalikan stoknya. Dicek tiap jam.
Schedule::command('orders:clean-unpaid')->hourly();
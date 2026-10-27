@extends('layouts.app')

@section('title', 'Arsip Order')

@section('content')
    <h2>Arsip Order</h2>
    <p>Order yang belum dibayar lebih dari 24 jam (atau dihapus manual) pindah ke sini. Stoknya sudah dikembalikan. Kalau pembeli bayar, klik <strong>Pulihkan</strong>.</p>

    {{-- Form pencarian: method GET supaya kata kuncinya muncul di alamat URL --}}
    <form method="GET" action="{{ route('archive.index') }}" style="margin: 12px 0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, no. HP, atau produk..." style="max-width: 300px;">
        <button type="submit">Cari</button>

        @if ($search !== '')
            <a href="{{ route('archive.index') }}">Reset</a>
        @endif
    </form>

    @if ($search !== '')
        <p>Ditemukan {{ $orders->total() }} order untuk "{{ $search }}".</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Produk</th>
                <th>Total</th>
                <th>Diarsipkan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->order_date->format('d/m/Y') }}</td>
                    <td>{{ $order->customer->name ?? $order->customer->phone ?? '-' }}</td>
                    <td style="text-align: left;">
                        @foreach ($order->items as $item)
                            <div>{{ $item->qty }}x {{ $item->product->name }}</div>
                        @endforeach
                    </td>
                    <td>Rp {{ number_format($order->items->sum(fn ($i) => $i->qty * $i->price), 0, ',', '.') }}</td>
                    <td>
                        {{ $order->deleted_at->format('d/m/Y H:i') }}<br>
                        <small>{{ $order->delete_reason === 'otomatis' ? 'otomatis (24 jam belum bayar)' : 'dihapus manual' }}</small>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('archive.restore', $order->id) }}" style="display:inline"
                              onsubmit="return confirm('Pulihkan order ini? Stok produk akan dikurangi lagi.')">
                            @csrf
                            <button type="submit">Pulihkan</button>
                        </form>
                        <form method="POST" action="{{ route('archive.destroy', $order->id) }}" style="display:inline"
                              onsubmit="return confirm('Hapus PERMANEN? Tidak bisa dikembalikan lagi.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #b8325a;">Hapus permanen</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">{{ $search !== '' ? 'Tidak ada order yang cocok.' : 'Arsip kosong.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $orders->links() }}</div>

    <script>
        // Ingat posisi scroll supaya halaman tidak loncat ke atas setelah Pulihkan / Hapus permanen.
        (function () {
            const KEY = 'arsip-scroll';

            // Kembalikan posisi yang tersimpan (sekali pakai, berlaku 15 menit).
            try {
                const saved = JSON.parse(sessionStorage.getItem(KEY) || 'null');
                if (saved && Date.now() - saved.time < 15 * 60 * 1000) {
                    window.scrollTo(0, saved.y);
                }
                sessionStorage.removeItem(KEY);
            } catch (e) { /* abaikan kalau penyimpanan browser tidak tersedia */ }

            // Simpan posisi saat tombol di dalam tabel dikirim (setelah konfirmasi di-OK).
            document.addEventListener('submit', function (e) {
                if (e.defaultPrevented || !e.target.closest('table')) return;
                try {
                    sessionStorage.setItem(KEY, JSON.stringify({ y: window.scrollY, time: Date.now() }));
                } catch (err) { /* abaikan */ }
            });
        })();
    </script>
@endsection
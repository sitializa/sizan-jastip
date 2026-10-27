@extends('layouts.app')

@section('title', 'Arsip Order')

@section('content')
    <h2>Arsip Order</h2>
    <p>Order yang belum dibayar lebih dari 24 jam (atau dihapus manual) pindah ke sini. Stoknya sudah dikembalikan. Kalau pembeli bayar, klik <strong>Pulihkan</strong>.</p>

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
                <tr><td colspan="7">Arsip kosong.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $orders->links() }}</div>
@endsection
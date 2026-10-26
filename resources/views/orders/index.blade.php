@extends('layouts.app')

@section('title', 'Daftar Order')

@section('content')
    <h2>Daftar Order</h2>
    <a href="{{ route('orders.create') }}">+ Buat Order</a>

    {{-- Form pencarian: method GET supaya kata kuncinya muncul di alamat URL --}}
    <form method="GET" action="{{ route('orders.index') }}" style="margin: 12px 0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau no. HP customer..." style="max-width: 300px;">
        <button type="submit">Cari</button>

        @if ($search !== '')
            <a href="{{ route('orders.index') }}">Reset</a>
        @endif
    </form>

    @if ($search !== '')
        <p>Ditemukan {{ $orders->total() }} order untuk "{{ $search }}".</p>
    @endif

    <table style="margin-top: 12px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Bayar</th>
                <th>Kirim</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->order_date->format('d/m/Y') }}</td>
                    <td>{{ $order->customer->name ?? $order->customer->phone }}</td>
                    <td>Rp {{ number_format($order->items->sum(fn ($i) => $i->qty * $i->price), 0, ',', '.') }}</td>
                    <td>{{ $order->payment_status }}</td>
                    <td>{{ $order->shipping_status }}</td>
                    <td><a href="{{ route('orders.show', $order) }}">Lihat</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        {{ $search !== '' ? 'Tidak ada order yang cocok.' : 'Belum ada order.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $orders->links() }}</div>
@endsection
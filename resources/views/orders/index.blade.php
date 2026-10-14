@extends('layouts.app')

@section('title', 'Daftar Order')

@section('content')
    <h2>Daftar Order</h2>
    <a href="{{ route('orders.create') }}">+ Buat Order</a>

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
                <tr><td colspan="7">Belum ada order.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $orders->links() }}</div>
@endsection
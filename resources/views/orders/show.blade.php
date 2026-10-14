@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
    <h2>Order #{{ $order->id }}</h2>
    <p>
        Customer: {{ $order->customer->phone }} {{ $order->customer->name ? '(' . $order->customer->name . ')' : '' }}<br>
        Tanggal: {{ $order->order_date->format('d/m/Y') }}<br>
        Status bayar: {{ $order->payment_status }} | Status kirim: {{ $order->shipping_status }}<br>
        Resi: {{ $order->tracking_number ?? '-' }}
    </p>

    <table>
        <thead>
            <tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th>Rp {{ number_format($order->items->sum(fn ($i) => $i->qty * $i->price), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <p><a href="{{ route('orders.index') }}">&larr; Kembali</a></p>
@endsection
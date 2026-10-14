@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
    <h2>Order #{{ $order->id }}</h2>

    <div style="margin: 16px 0;">
        Customer: {{ $order->customer->phone }} {{ $order->customer->name ? '(' . $order->customer->name . ')' : '' }}<br>
        Tanggal: {{ $order->order_date->format('d/m/Y') }}

        <div style="display: flex; align-items: center; gap: 12px;">
            <span>Status bayar: {{ $order->payment_status }}</span>

            <form method="POST" action="{{ route('orders.payment', $order) }}" style="margin: 0;">
                @csrf
                @method('PATCH')

                <input type="hidden" name="paid" value="0">
                <label style="display: inline; margin: 0;">
                    <input type="checkbox" name="paid" value="1"
                           style="width: auto;"
                           onchange="this.form.submit()"
                           @checked($order->payment_status === 'sudah')>
                    Sudah dibayar
                </label>
            </form>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <span>Status kirim: {{ $order->shipping_status }}</span>

            <form method="POST" action="{{ route('orders.shipping', $order) }}" style="margin: 0;">
                @csrf
                @method('PATCH')

                <input type="hidden" name="shipped" value="0">
                <label style="display: inline; margin: 0;">
                    <input type="checkbox" name="shipped" value="1"
                           style="width: auto;"
                           onchange="this.form.submit()"
                           @checked($order->shipping_status === 'sudah')>
                    Sudah dikirim
                </label>
            </form>
        </div>

        Resi: {{ $order->tracking_number ?? '-' }}
    </div>

    <form method="POST" action="{{ route('orders.tracking', $order) }}">
        @csrf
        @method('PATCH')

        <label for="tracking_number">Nomor resi (opsional)</label>
        <input type="text" name="tracking_number" id="tracking_number"
               value="{{ old('tracking_number', $order->tracking_number) }}">
        @error('tracking_number')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit" style="margin-top: 8px;">Simpan resi</button>
    </form>

    <table style="margin-top: 16px;">
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
@extends('layouts.app')

@section('title', 'Pembelian #' . $purchase->id)

@section('content')
    <h2>Pembelian #{{ $purchase->id }}</h2>

    <div style="margin: 16px 0;">
        Supplier: {{ $purchase->supplier->name }}<br>
        Kontak: {{ $purchase->supplier->contact ?? '-' }}<br>
        Tanggal: {{ $purchase->purchase_date->format('d/m/Y') }}
    </div>

    @php
        $subtotal = $purchase->items->sum(fn ($i) => $i->qty * $i->price);
    @endphp

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga beli</th>
                <th>Harga modal bersih</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->net_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Subtotal barang</th>
                <th>Rp {{ number_format($subtotal, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="4">Biaya tambahan (ongkir, packing)</th>
                <th>Rp {{ number_format($purchase->extra_cost, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="4">Total dibayar ke supplier</th>
                <th>Rp {{ number_format($subtotal + $purchase->extra_cost, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <p style="font-size: 14px;">
        Harga modal bersih = harga beli + biaya tambahan dibagi rata ke semua pcs dalam pembelian ini.
    </p>

    <p><a href="{{ route('purchases.index') }}">&larr; Kembali</a></p>
@endsection
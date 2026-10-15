@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@section('content')
    <h2>Daftar Pembelian</h2>
    <a href="{{ route('purchases.create') }}">+ Catat Pembelian</a>

    <table style="margin-top: 12px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Total (termasuk biaya tambahan)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->id }}</td>
                    <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>Rp {{ number_format($purchase->items->sum(fn ($i) => $i->qty * $i->price) + $purchase->extra_cost, 0, ',', '.') }}</td>
                    <td><a href="{{ route('purchases.show', $purchase) }}">Lihat</a></td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada pembelian.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $purchases->links() }}</div>
@endsection
@extends('layouts.app')

@section('title', 'Daftar Produk')

@section('content')
    <h2>Daftar Produk</h2>
    <a href="{{ route('products.create') }}">+ Tambah Produk</a>

    <table style="margin-top: 12px;">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Brand</th>
                <th>Kategori</th>
                <th>Modal</th>
                <th>Jual</th>
                <th>Stok</th>
                <th>Expired</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->brand ?? '-' }}</td>
                    <td>{{ $product->category }}</td>
                    <td>Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($product->sell_price, 0, ',', '.') }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->expired_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        <a href="{{ route('products.edit', $product) }}">Ubah</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">Belum ada produk.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $products->links() }}</div>
@endsection
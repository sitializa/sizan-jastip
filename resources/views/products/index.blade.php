@extends('layouts.app')

@section('title', 'Daftar Produk')

@section('content')
    <h2>Daftar Produk</h2>
    <a href="{{ route('products.create') }}">+ Tambah Produk</a>

    <form method="GET" action="{{ route('products.index') }}" style="margin: 12px 0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau brand..." style="max-width: 280px;">

        <select name="kategori" style="width: auto;">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected($category === $cat)>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>

        <label style="display: inline-flex; align-items: center; gap: 6px; margin: 0;">
            <input type="checkbox" name="cek" value="1" style="width: auto;" @checked($onlyCheck)>
            ⚠ Perlu dicek
        </label>

        <button type="submit">Cari</button>

        @if ($search !== '' || $category || $onlyCheck)
            <a href="{{ route('products.index') }}">Reset</a>
        @endif
    </form>

    @if ($search !== '' || $category || $onlyCheck)
        <p>Ditemukan {{ $products->total() }} produk.</p>
    @endif

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
                <tr @style(['background: #fff6e0' => $product->needs_check])>
                    <td>
                        @if ($product->needs_check)
                            <span title="Cek lagi nanti" style="color: #d97706;">⚠</span>
                        @endif
                        {{ $product->name }}
                    </td>
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
                <tr>
                    <td colspan="8">
                        {{ ($search !== '' || $category || $onlyCheck) ? 'Tidak ada produk yang cocok.' : 'Belum ada produk.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $products->links() }}</div>
@endsection
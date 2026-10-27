@extends('layouts.app')

@section('title', 'Rekap Produk')

@section('content')
    <h2>Rekap Produk</h2>
    <p>Order yang belum dibayar <strong>tetap dihitung</strong> sebagai terjual.</p>

    <style>
        .cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .card {
            background: #fff; border: 1px solid #f6d5e3; border-radius: 12px;
            padding: 12px 16px; min-width: 190px;
        }
        .card .label { color: #7d6b74; font-size: 14px; }
        .card .value { font-size: 22px; font-weight: 700; color: #c9507f; }
        .card .sub { font-size: 13px; color: #7d6b74; }
    </style>

    <div class="cards">
        <div class="card">
            <div class="label">Total produk</div>
            <div class="value">{{ $jumlahProduk }}</div>
            <div class="sub">{{ $sudahTerjual->count() }} sudah terjual, {{ $belumTerjual->count() }} belum</div>
        </div>
        <div class="card">
            <div class="label">Total masuk (pcs)</div>
            <div class="value">{{ $totalMasuk }}</div>
            <div class="sub">terjual + sisa stok</div>
        </div>
        <div class="card">
            <div class="label">Sudah terjual (pcs)</div>
            <div class="value">{{ $totalTerjual }}</div>
            <div class="sub">{{ $persenLaku }}% dari total masuk</div>
        </div>
        <div class="card">
            <div class="label">Belum terjual (pcs)</div>
            <div class="value">{{ $totalSisa }}</div>
            <div class="sub">sisa stok saat ini</div>
        </div>
        <div class="card">
            <div class="label">Omzet produk terjual</div>
            <div class="value">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Nilai stok (modal)</div>
            <div class="value">Rp {{ number_format($nilaiStok, 0, ',', '.') }}</div>
            <div class="sub">sisa stok x harga modal</div>
        </div>
    </div>

    <h3>Sudah terjual ({{ $sudahTerjual->count() }} produk)</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Brand</th>
                <th>Kategori</th>
                <th>Terjual</th>
                <th>Sisa stok</th>
                <th>Omzet</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sudahTerjual as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->brand ?? '-' }}</td>
                    <td>{{ $p->category }}</td>
                    <td><strong>{{ (int) $p->terjual }}</strong></td>
                    <td>{{ $p->stock }}</td>
                    <td>Rp {{ number_format($p->omzet, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada produk yang terjual.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top: 28px;">Belum terjual ({{ $belumTerjual->count() }} produk)</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Brand</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Modal</th>
                <th>Nilai stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($belumTerjual as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->brand ?? '-' }}</td>
                    <td>{{ $p->category }}</td>
                    <td>{{ $p->stock }}</td>
                    <td>Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($p->stock * $p->cost_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Semua produk sudah pernah terjual.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
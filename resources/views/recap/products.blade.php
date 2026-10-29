@extends('layouts.app')

@section('title', 'Rekap Produk')

@section('content')
    <h2>Rekap Produk</h2>
    <p>"Sudah terjual" menghitung semua order, <strong>termasuk yang belum dibayar</strong>. "Sudah dibayar" hanya order yang sudah dicentang dibayar.</p>

    <style>
        .cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .card {
            background: #fff; border: 1px solid #f6d5e3; border-radius: 12px;
            padding: 12px 16px; min-width: 190px;
        }
        .card.total { background: #fdeef4; border-color: #f0b3cc; }
        .card .label { color: #7d6b74; font-size: 14px; }
        .card .value { font-size: 22px; font-weight: 700; color: #c9507f; }
        .card .sub { font-size: 13px; color: #7d6b74; }
    </style>

    {{-- Form pencarian: method GET supaya kata kuncinya muncul di alamat URL --}}
    <form method="GET" action="{{ route('recap.products') }}" style="margin: 12px 0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau brand..." style="max-width: 280px;">

        <select name="kategori" style="width: auto;">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected($category === $cat)>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>

        <button type="submit">Cari</button>

        @if ($search !== '' || $category)
            <a href="{{ route('recap.products') }}">Reset</a>
        @endif
    </form>

    @if ($search !== '' || $category)
        <p>Menampilkan {{ $jumlahProduk }} produk. Kartu di bawah ikut menghitung hasil pencarian saja.</p>
    @endif

    <div class="cards">
        <div class="card total">
            <div class="label">TOTAL (terjual + belum terjual)</div>
            <div class="value">{{ $totalMasuk }} pcs</div>
            <div class="sub">{{ $totalTerjual }} terjual + {{ $totalSisa }} belum terjual</div>
        </div>
        <div class="card">
            <div class="label">Total produk</div>
            <div class="value">{{ $jumlahProduk }}</div>
            <div class="sub">{{ $sudahTerjual->count() }} sudah terjual, {{ $belumTerjual->count() }} belum</div>
        </div>
        <div class="card">
            <div class="label">Sudah terjual (pcs)</div>
            <div class="value">{{ $totalTerjual }}</div>
            <div class="sub">{{ $persenLaku }}% dari total</div>
        </div>
        <div class="card">
            <div class="label">Sudah dibayar (pcs)</div>
            <div class="value">{{ $totalDibayar }}</div>
            <div class="sub">uang masuk Rp {{ number_format($totalOmzetDibayar, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Terjual, belum dibayar (pcs)</div>
            <div class="value">{{ $totalBelumDibayar }}</div>
            <div class="sub">menggantung Rp {{ number_format($totalOmzet - $totalOmzetDibayar, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Belum terjual (pcs)</div>
            <div class="value">{{ $totalSisa }}</div>
            <div class="sub">sisa stok saat ini</div>
        </div>
        <div class="card">
            <div class="label">Omzet semua yang terjual</div>
            <div class="value">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Nilai stok (modal)</div>
            <div class="value">Rp {{ number_format($nilaiStok, 0, ',', '.') }}</div>
            <div class="sub">sisa stok x harga modal</div>
        </div>
    </div>

    <h3>Sudah dibayar ({{ $sudahDibayar->count() }} produk)</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Brand</th>
                <th>Kategori</th>
                <th>Pcs dibayar</th>
                <th>Uang masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sudahDibayar as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->brand ?? '-' }}</td>
                    <td>{{ $p->category }}</td>
                    <td><strong>{{ (int) $p->dibayar }}</strong></td>
                    <td>Rp {{ number_format($p->omzet_dibayar, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ ($search !== '' || $category) ? 'Tidak ada yang cocok.' : 'Belum ada order yang dibayar.' }}</td></tr>
            @endforelse
        </tbody>
        @if ($sudahDibayar->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>{{ $totalDibayar }}</th>
                    <th>Rp {{ number_format($totalOmzetDibayar, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>

    <h3 style="margin-top: 28px;">Sudah terjual, semua order ({{ $sudahTerjual->count() }} produk)</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Brand</th>
                <th>Kategori</th>
                <th>Terjual</th>
                <th>Sudah dibayar</th>
                <th>Belum dibayar</th>
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
                    <td>{{ (int) $p->dibayar }}</td>
                    <td>
                        @if ((int) $p->terjual - (int) $p->dibayar > 0)
                            <span class="error">{{ (int) $p->terjual - (int) $p->dibayar }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $p->stock }}</td>
                    <td>Rp {{ number_format($p->omzet, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">{{ ($search !== '' || $category) ? 'Tidak ada yang cocok.' : 'Belum ada produk yang terjual.' }}</td></tr>
            @endforelse
        </tbody>
        @if ($sudahTerjual->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>{{ $totalTerjual }}</th>
                    <th>{{ $totalDibayar }}</th>
                    <th>{{ $totalBelumDibayar }}</th>
                    <th>{{ $sudahTerjual->sum('stock') }}</th>
                    <th>Rp {{ number_format($totalOmzet, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
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
                <tr><td colspan="6">{{ ($search !== '' || $category) ? 'Tidak ada yang cocok.' : 'Semua produk sudah pernah terjual.' }}</td></tr>
            @endforelse
        </tbody>
        @if ($belumTerjual->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>{{ $belumTerjual->sum('stock') }}</th>
                    <th></th>
                    <th>Rp {{ number_format($belumTerjual->sum(fn ($p) => $p->stock * $p->cost_price), 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
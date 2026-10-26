@extends('layouts.app')

@section('title', 'Penjualan Harian')

@section('content')
    <h2>Penjualan Harian</h2>
    <p>Semua order dihitung, <strong>termasuk yang belum dibayar</strong>. Kolom "Belum dibayar" menunjukkan uang yang masih menggantung.</p>

    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
        <div style="background: #fff; border: 1px solid #f6d5e3; border-radius: 12px; padding: 12px 16px; min-width: 200px;">
            <div style="color: #7d6b74; font-size: 14px;">Hari ini</div>
            <div style="font-size: 22px; font-weight: 700; color: #c9507f;">
                Rp {{ number_format($hariIni->total, 0, ',', '.') }}
            </div>
            <div>{{ $hariIni->jumlah_order }} order, {{ $hariIni->jumlah_pcs }} pcs</div>
        </div>

        <div style="background: #fff; border: 1px solid #f6d5e3; border-radius: 12px; padding: 12px 16px; min-width: 200px;">
            <div style="color: #7d6b74; font-size: 14px;">Total {{ $namaBulan }}</div>
            <div style="font-size: 22px; font-weight: 700; color: #c9507f;">
                Rp {{ number_format($totalRupiah, 0, ',', '.') }}
            </div>
            <div>{{ $totalOrder }} order, {{ $totalPcs }} pcs</div>
        </div>
    </div>

    <form method="GET" action="{{ route('sales.daily') }}" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 12px;">
        <label style="margin: 0;">Bulan</label>
        <input type="month" name="bulan" value="{{ $bulan }}" style="width: auto;">
        <button type="submit">Tampilkan</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Order</th>
                <th>Pcs terjual</th>
                <th>Total penjualan</th>
                <th>Sudah dibayar</th>
                <th>Belum dibayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->label }}</td>
                    <td>{{ $row->jumlah_order }}</td>
                    <td>{{ $row->jumlah_pcs }}</td>
                    <td><strong>Rp {{ number_format($row->total, 0, ',', '.') }}</strong></td>
                    <td>Rp {{ number_format($row->sudah_bayar, 0, ',', '.') }}</td>
                    <td>
                        @if ($row->belum_bayar > 0)
                            <span class="error">Rp {{ number_format($row->belum_bayar, 0, ',', '.') }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada order di {{ $namaBulan }}.</td></tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <th>Total {{ $namaBulan }}</th>
                    <th>{{ $totalOrder }}</th>
                    <th>{{ $totalPcs }}</th>
                    <th>Rp {{ number_format($totalRupiah, 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($totalBayar, 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($totalBelum, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
@extends('layouts.app')

@section('title', 'Laporan Keuntungan')

@section('content')
    <h2>Laporan Keuntungan</h2>
    <p>Dihitung dari order yang <strong>sudah dibayar</strong>, memakai harga jual dan modal saat order dibuat.</p>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Order</th>
                <th>Pcs</th>
                <th>Omzet</th>
                <th>Modal</th>
                <th>Untung</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->nama_bulan }}</td>
                    <td>{{ $row->jumlah_order }}</td>
                    <td>{{ $row->jumlah_pcs }}</td>
                    <td>Rp {{ number_format($row->omzet, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row->modal, 0, ',', '.') }}</td>
                    <td><strong>Rp {{ number_format($row->untung, 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada order yang dibayar.</td></tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>Rp {{ number_format($totalOmzet, 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($totalModal, 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($totalUntung, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
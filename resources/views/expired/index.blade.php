@extends('layouts.app')

@section('title', 'Reminder Expired')

@section('content')
    <h2>Reminder Expired</h2>

    <form method="GET" action="{{ route('expired.index') }}">
        <label style="display: inline;">Tampilkan yang expired dalam
            <select name="hari" style="width: auto;" onchange="this.form.submit()">
                @foreach ([30, 60, 90, 180, 365] as $option)
                    <option value="{{ $option }}" @selected($days == $option)>{{ $option }} hari</option>
                @endforeach
            </select>
            ke depan
        </label>
    </form>

    <p style="font-size: 14px;">
        Merah = sudah expired atau expired hari ini. Oranye = sisa 30 hari atau kurang.
        Produk yang stoknya 0 tidak ditampilkan.
    </p>

    <table style="margin-top: 12px;">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Brand</th>
                <th>Stok</th>
                <th>Tanggal expired</th>
                <th>Sisa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                @php
                    $left = $product->days_left;
                    $rowStyle = $left <= 0 ? 'background: #fee2e2;' : ($left <= 30 ? 'background: #ffedd5;' : '');
                @endphp
                <tr style="{{ $rowStyle }}">
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->brand ?? '-' }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->expired_date->format('d/m/Y') }}</td>
                    <td>
                        @if ($left < 0)
                            Sudah expired {{ abs($left) }} hari lalu
                        @elseif ($left === 0)
                            Expired hari ini
                        @else
                            {{ $left }} hari lagi
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada produk yang mendekati expired dalam {{ $days }} hari ke depan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 12px;">Total {{ $products->count() }} produk.</p>
@endsection
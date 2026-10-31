@extends('layouts.app')

@section('title', 'Pivot Pesanan')

@section('content')
    <h2>Pivot Pesanan</h2>
    <p>Pesanan siapa saja yang sudah dikirim, belum dikirim, sudah dibayar, belum dibayar, dan produk dipesan oleh siapa. Order di Arsip tidak ikut dihitung.</p>

    <form method="GET" action="{{ route('pivot.index') }}" style="margin: 12px 0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, no. HP, atau produk..." style="max-width: 300px;">
        <button type="submit">Cari</button>

        @if ($search !== '')
            <a href="{{ route('pivot.index') }}">Reset</a>
        @endif
    </form>

    @if ($search !== '')
        <p>Menampilkan {{ $totalOrder }} order yang cocok dengan "{{ $search }}". Semua angka di halaman ini hanya menghitung order tersebut. Order yang cocok ditampilkan lengkap dengan semua produknya.</p>
    @endif

    <p>
        Loncat ke:
        <a href="#ringkasan">Ringkasan</a> |
        <a href="#dikirim">Sudah dikirim</a> |
        <a href="#belum-dikirim">Belum dikirim</a> |
        <a href="#dibayar">Sudah dibayar</a> |
        <a href="#belum-dibayar">Belum dibayar</a> |
        <a href="#per-customer">Per customer</a> |
        <a href="#per-produk">Per produk</a>
    </p>

    {{-- 1. Matriks bayar x kirim --}}
    <h3 id="ringkasan">Ringkasan bayar x kirim</h3>
    <table>
        <thead>
            <tr>
                <th></th>
                <th>Sudah dikirim</th>
                <th>Belum dikirim</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['sudah' => 'Sudah dibayar', 'belum' => 'Belum dibayar'] as $bayar => $labelBayar)
                <tr>
                    <th>{{ $labelBayar }}</th>
                    @foreach (['sudah', 'belum'] as $kirim)
                        <td>
                            <strong>{{ $matrix[$bayar][$kirim]->order }} order</strong><br>
                            Rp {{ number_format($matrix[$bayar][$kirim]->total, 0, ',', '.') }}
                        </td>
                    @endforeach
                    <td>
                        <strong>{{ $matrix[$bayar]['sudah']->order + $matrix[$bayar]['belum']->order }} order</strong><br>
                        Rp {{ number_format($matrix[$bayar]['sudah']->total + $matrix[$bayar]['belum']->total, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 2. Empat daftar customer --}}
    @foreach ($sections as $s)
        <h3 id="{{ $s['id'] }}" style="margin-top: 28px;">
            {{ $s['title'] }}
            ({{ $s['groups']->count() }} customer, {{ $s['groups']->sum(fn ($g) => $g->orders->count()) }} order)
        </h3>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Order</th>
                    <th>Pcs</th>
                    <th>Total</th>
                    @if ($s['tagihan'])
                        <th>Tagihan</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($s['groups'] as $g)
                    <tr>
                        <td>{{ $g->name }}{{ $g->phone && $g->name !== $g->phone ? ' (' . $g->phone . ')' : '' }}</td>
                        <td>
                            @foreach ($g->orders as $o)
                                <a href="{{ route('orders.show', $o) }}">#{{ $o->id }}</a>
                                <small>({{ $o->order_date->format('d/m') }})</small>@if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td>{{ $g->pcs }}</td>
                        <td>Rp {{ number_format($g->total, 0, ',', '.') }}</td>
                        @if ($s['tagihan'])
                            <td>
                                <a href="{{ route('customers.invoice', ['customer' => $g->id, 'status' => 'belum']) }}" target="_blank">Tagihan</a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $s['tagihan'] ? 5 : 4 }}">Tidak ada.</td></tr>
                @endforelse
            </tbody>
            @if ($s['groups']->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="2">Total</th>
                        <th>{{ $s['groups']->sum('pcs') }}</th>
                        <th>Rp {{ number_format($s['groups']->sum('total'), 0, ',', '.') }}</th>
                        @if ($s['tagihan'])
                            <th></th>
                        @endif
                    </tr>
                </tfoot>
            @endif
        </table>
    @endforeach

    {{-- 3. Ringkasan per customer --}}
    <h3 id="per-customer" style="margin-top: 28px;">Ringkasan per customer ({{ $perCustomer->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Order</th>
                <th>Pcs</th>
                <th>Total belanja</th>
                <th>Sudah dibayar</th>
                <th>Belum dibayar</th>
                <th>Pcs dikirim</th>
                <th>Pcs belum dikirim</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perCustomer as $c)
                <tr>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->jumlah_order }}</td>
                    <td>{{ $c->pcs }}</td>
                    <td>Rp {{ number_format($c->total, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($c->dibayar, 0, ',', '.') }}</td>
                    <td>
                        @if ($c->belum_bayar > 0)
                            <span class="error">Rp {{ number_format($c->belum_bayar, 0, ',', '.') }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $c->pcs_dikirim }}</td>
                    <td>
                        @if ($c->pcs_belum_kirim > 0)
                            <span class="error">{{ $c->pcs_belum_kirim }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">Belum ada order.</td></tr>
            @endforelse
        </tbody>
        @if ($perCustomer->isNotEmpty())
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>{{ $perCustomer->sum('jumlah_order') }}</th>
                    <th>{{ $perCustomer->sum('pcs') }}</th>
                    <th>Rp {{ number_format($perCustomer->sum('total'), 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($perCustomer->sum('dibayar'), 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($perCustomer->sum('belum_bayar'), 0, ',', '.') }}</th>
                    <th>{{ $perCustomer->sum('pcs_dikirim') }}</th>
                    <th>{{ $perCustomer->sum('pcs_belum_kirim') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- 4. Produk dipesan oleh siapa saja --}}
    <h3 id="per-produk" style="margin-top: 28px;">Produk dipesan oleh siapa saja ({{ $perProduct->count() }} produk)</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Total pcs</th>
                <th>Dipesan oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perProduct as $p)
                <tr>
                    <td>{{ $p->name }}{{ $p->brand ? ' (' . $p->brand . ')' : '' }}</td>
                    <td><strong>{{ $p->pcs }}</strong></td>
                    <td>
                        @foreach ($p->buyers as $b)
                            <div>
                                {{ $b->name }} x{{ $b->qty }}
                                <a href="{{ route('orders.show', $b->order) }}"><small>#{{ $b->order->id }}</small></a>
                                <small>
                                    &middot; {{ $b->paid ? 'lunas' : 'belum bayar' }}
                                    &middot; {{ $b->shipped ? 'dikirim' : 'belum dikirim' }}
                                </small>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada order.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
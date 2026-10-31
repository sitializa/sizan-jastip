@extends('layouts.app')

@section('title', 'Pivot Pesanan')

@section('content')
    <h2>Pivot Pesanan</h2>
    <p>Pesanan siapa saja yang sudah dikirim, belum dikirim, sudah dibayar, belum dibayar, dan produk dipesan oleh siapa. Order di Arsip tidak ikut dihitung.</p>

    <style>
        .pill { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .pill.ok { color: #1e6b3c; background: #e6f6ec; }
        .pill.no { color: #b8325a; background: #fbe7ee; }
        .detail-row td { background: #fffafc; text-align: left; }
        .detail-order { margin-bottom: 14px; }
        .detail-order table { margin-top: 6px; }
        .btn-small { padding: 4px 10px; font-size: 13px; }
    </style>

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

    <p>
        <button type="button" class="btn-small" id="open-all">Buka semua rincian</button>
        <button type="button" class="btn-small" id="close-all">Tutup semua rincian</button>
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

    {{-- 2. Empat daftar customer, rincian dibuka lewat tombol di kolom Aksi --}}
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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($s['groups'] as $g)
                    @php $detailId = 'd-' . $s['id'] . '-' . $g->id; @endphp
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
                        <td>
                            <button type="button" class="btn-small" data-toggle="{{ $detailId }}">Rincian</button>
                            @if ($s['tagihan'])
                                <a href="{{ route('customers.invoice', ['customer' => $g->id, 'status' => 'belum']) }}" target="_blank">Tagihan</a>
                            @endif
                        </td>
                    </tr>

                    {{-- Baris rincian: tersembunyi sampai tombol Rincian diklik --}}
                    <tr class="detail-row" id="{{ $detailId }}" style="display: none;">
                        <td colspan="5">
                            @foreach ($g->orders as $o)
                                <div class="detail-order">
                                    <strong><a href="{{ route('orders.show', $o) }}">Order #{{ $o->id }}</a></strong>
                                    &middot; {{ $o->order_date->format('d/m/Y') }}
                                    <span class="pill {{ $o->payment_status === 'sudah' ? 'ok' : 'no' }}">
                                        {{ $o->payment_status === 'sudah' ? 'Lunas' : 'Belum dibayar' }}
                                    </span>
                                    <span class="pill {{ $o->shipping_status === 'sudah' ? 'ok' : 'no' }}">
                                        {{ $o->shipping_status === 'sudah' ? 'Sudah dikirim' : 'Belum dikirim' }}
                                    </span>
                                    @if ($o->tracking_number)
                                        <small>&middot; Resi: {{ $o->tracking_number }}</small>
                                    @endif

                                    <table>
                                        <thead>
                                            <tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($o->items as $item)
                                                <tr>
                                                    <td>{{ $item->product->name }}{{ $item->product->brand ? ' (' . $item->product->brand . ')' : '' }}</td>
                                                    <td>{{ $item->qty }}</td>
                                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Total order ini</th>
                                                <th>Rp {{ number_format($o->total, 0, ',', '.') }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endforeach

                            <div>
                                <a href="{{ route('customers.invoice', $g->id) }}" target="_blank">Invoice semua order customer ini</a>
                                |
                                <a href="{{ route('customers.invoice', ['customer' => $g->id, 'status' => 'belum']) }}" target="_blank">Tagihan belum dibayar</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Tidak ada.</td></tr>
                @endforelse
            </tbody>
            @if ($s['groups']->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="2">Total</th>
                        <th>{{ $s['groups']->sum('pcs') }}</th>
                        <th>Rp {{ number_format($s['groups']->sum('total'), 0, ',', '.') }}</th>
                        <th></th>
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

    <script>
        // Tombol Rincian: buka atau tutup baris rincian milik customer itu.
        function setDetail(row, open) {
            row.style.display = open ? 'table-row' : 'none';
            const btn = document.querySelector('[data-toggle="' + row.id + '"]');
            if (btn) btn.textContent = open ? 'Tutup' : 'Rincian';
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-toggle]');
            if (!btn) return;
            const row = document.getElementById(btn.dataset.toggle);
            setDetail(row, row.style.display === 'none');
        });

        document.getElementById('open-all').addEventListener('click', function () {
            document.querySelectorAll('.detail-row').forEach(r => setDetail(r, true));
        });

        document.getElementById('close-all').addEventListener('click', function () {
            document.querySelectorAll('.detail-row').forEach(r => setDetail(r, false));
        });
    </script>
@endsection
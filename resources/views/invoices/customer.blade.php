<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Invoice {{ $customer->name ?? $customer->phone }}</title>
    <style>
        :root {
            --pink-50: #fffafc;
            --pink-100: #fdeef4;
            --pink-300: #f0b3cc;
            --pink-400: #e9809f;
            --pink-500: #c9507f;
            --text: #3f3339;
            --text-soft: #7d6b74;
            --head-bg: #f1edef;
            --grid: #cfc2c9;
            --ok: #1e6b3c;
            --ok-bg: #e6f6ec;
            --bad: #b8325a;
            --bad-bg: #fbe7ee;
        }

        * { box-sizing: border-box; }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            max-width: 760px;
            margin: 24px auto;
            padding: 0 16px;
            color: var(--text);
            background: #fff;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .actions { margin-bottom: 16px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .actions a { color: var(--pink-500); text-decoration: none; }
        .actions a.on { font-weight: 700; text-decoration: underline; }
        .actions button {
            background: var(--pink-400); color: #fff; border: 0;
            padding: 7px 14px; border-radius: 8px; font: inherit; cursor: pointer;
        }

        .header {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding-bottom: 12px; border-bottom: 3px solid var(--pink-300);
        }
        h1 { margin: 0; color: var(--pink-500); }
        .title { color: var(--text-soft); letter-spacing: 2px; }
        .right { text-align: right; }

        .badge {
            display: inline-block; padding: 3px 10px; margin-left: 6px;
            border-radius: 999px; font-weight: bold; font-size: 12px;
            color: var(--ok); background: var(--ok-bg);
        }
        .badge.belum { color: var(--bad); background: var(--bad-bg); }

        .ship { font-size: 13px; font-weight: 600; color: var(--ok); }
        .ship.no { color: var(--bad); }

        .order { margin-top: 20px; page-break-inside: avoid; }
        .order h3 { margin: 0 0 6px; font-size: 16px; }
        .resi { font-size: 13px; color: var(--text-soft); margin-bottom: 6px; }

        table {
            border-collapse: collapse; width: 100%;
            border: 1px solid var(--grid);
        }
        th, td { border: 1px solid var(--grid); padding: 7px 10px; text-align: center; }
        td:first-child, thead th:first-child { text-align: left; }
        thead th { background: var(--pink-100); color: var(--text); border-bottom: 2px solid var(--pink-300); }
        tbody tr:nth-child(even) { background: var(--pink-50); }
        tfoot th { background: var(--head-bg); }

        .summary {
            margin-top: 24px; padding: 12px 16px;
            background: var(--pink-50); border: 2px solid var(--pink-300);
            border-radius: 10px; page-break-inside: avoid;
        }
        .summary .row { display: flex; justify-content: space-between; padding: 2px 0; }
        .summary .grand {
            border-top: 1px solid var(--pink-300); margin-top: 6px; padding-top: 6px;
            font-size: 18px; font-weight: 700; color: var(--pink-500);
        }

        .payment {
            margin-top: 20px; padding: 12px 16px;
            background: var(--pink-50); border: 1px solid var(--pink-300);
            border-radius: 10px; font-size: 14px;
            display: flex; gap: 20px; align-items: flex-start; justify-content: space-between;
            page-break-inside: avoid;
        }
        .payment .label { font-style: italic; color: var(--text-soft); margin-bottom: 4px; }
        .payment .list div { margin-bottom: 2px; }
        .payment .fee { margin-top: 8px; font-size: 13px; color: var(--pink-500); font-weight: 600; }
        .payment .qris { text-align: center; flex-shrink: 0; }
        .payment .qris img { width: 200px; height: auto; display: block; border: 1px solid var(--pink-300); }

        .thanks { margin-top: 24px; text-align: center; color: var(--pink-500); }

        @media print {
            .actions { display: none; }
            body { margin: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route('customers.invoice', $customer) }}" @class(['on' => $status === 'semua'])>Semua order</a>
        <a href="{{ route('customers.invoice', ['customer' => $customer, 'status' => 'belum']) }}" @class(['on' => $status === 'belum'])>Hanya yang belum dibayar</a>
        <a href="{{ route('customers.index') }}">&larr; Kembali</a>
    </div>

    <div class="header">
        <div>
            <h1>{{ config('shop.name') }}</h1>
            <div class="title">{{ $status === 'belum' ? 'TAGIHAN BELUM DIBAYAR' : 'REKAP INVOICE' }}</div>
        </div>
        <div class="right">
            <strong>REK-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</strong><br>
            Dicetak: {{ now()->format('d/m/Y') }}
            @if ($orders->isNotEmpty())
                <br>Periode: {{ $orders->first()->order_date->format('d/m/Y') }} - {{ $orders->last()->order_date->format('d/m/Y') }}
            @endif
        </div>
    </div>

    <p>
        <strong>Kepada:</strong><br>
        {{ $customer->name ?? '-' }}<br>
        {{ $customer->phone ?? '-' }}
    </p>

    @forelse ($orders as $order)
        <div class="order">
            <h3>
                {{ $order->order_date->translatedFormat('l, d F Y') }}
                <span style="color: var(--text-soft); font-weight: normal;">(INV-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }})</span>
                <span class="badge {{ $order->payment_status === 'sudah' ? '' : 'belum' }}">
                    {{ $order->payment_status === 'sudah' ? 'LUNAS' : 'BELUM DIBAYAR' }}
                </span>
            </h3>

            @if ($order->tracking_number)
                <div class="resi">No. resi: {{ $order->tracking_number }}</div>
            @endif

            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Pengiriman</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}{{ $item->product->brand ? ' (' . $item->product->brand . ')' : '' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
                            <td>
                                @if ($order->shipping_status === 'sudah')
                                    <span class="ship">&#10003; Sudah dikirim</span>
                                @else
                                    <span class="ship no">Belum dikirim</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total order ini</th>
                        <th>Rp {{ number_format($order->total, 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <p style="margin-top: 24px;">
            {{ $status === 'belum' ? 'Tidak ada order yang belum dibayar. Semua sudah lunas.' : 'Customer ini belum punya order.' }}
        </p>
    @endforelse

    @if ($orders->isNotEmpty())
        <div class="summary">
            <div class="row"><span>Jumlah order</span><span>{{ $orders->count() }}</span></div>
            <div class="row"><span>Jumlah barang</span><span>{{ $totalPcs }} pcs</span></div>
            <div class="row"><span>Barang sudah dikirim</span><span>{{ $pcsDikirim }} pcs</span></div>
            <div class="row"><span>Barang belum dikirim</span><span>{{ $pcsBelumKirim }} pcs</span></div>
            <div class="row"><span>Total belanja</span><span>Rp {{ number_format($total, 0, ',', '.') }}</span></div>
            @if ($status === 'semua')
                <div class="row"><span>Sudah dibayar</span><span>Rp {{ number_format($sudahBayar, 0, ',', '.') }}</span></div>
            @endif
            <div class="row grand">
                <span>{{ $belumBayar > 0 ? 'Yang harus dibayar' : 'Sisa tagihan' }}</span>
                <span>Rp {{ number_format($belumBayar, 0, ',', '.') }}</span>
            </div>
        </div>

        @if ($belumBayar > 0)
            @php
                // Cari gambar QRIS di public/images (png, jpg, atau jpeg).
                $qris = collect(['png', 'jpg', 'jpeg'])
                    ->first(fn ($ext) => file_exists(public_path("images/qris.$ext")));
            @endphp

            <div class="payment">
                <div class="list">
                    <div class="label">PAYMENT :</div>
                    @foreach (config('shop.accounts') as $acc)
                        @if ($acc['number'])
                            <div>
                                {{ $acc['bank'] }} {{ $acc['number'] }}
                                @if ($acc['name']) a.n. {{ $acc['name'] }} @endif
                                @if ($acc['note']) ({{ $acc['note'] }}) @endif
                            </div>
                        @endif
                    @endforeach
                    <div>QRIS (scan di samping)</div>
                    <div class="fee">{{ config('shop.fee_note') }}</div>
                </div>

                @if ($qris)
                    <div class="qris">
                        <img src="{{ asset("images/qris.$qris") }}" alt="QRIS">
                    </div>
                @endif
            </div>
        @endif
    @endif

    <p class="thanks">Terima kasih sudah belanja di {{ config('shop.name') }}.</p>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice INV-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</title>
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
            max-width: 720px;
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
            display: inline-block; padding: 3px 10px; margin-top: 4px;
            border-radius: 999px; font-weight: bold; font-size: 13px;
            color: var(--ok); background: var(--ok-bg);
        }
        .badge.belum { color: var(--bad); background: var(--bad-bg); }

        table {
            border-collapse: collapse; width: 100%; margin-top: 16px;
            border: 1px solid var(--grid);
        }
        th, td { border: 1px solid var(--grid); padding: 8px 10px; text-align: center; }
        td:first-child, thead th:first-child { text-align: left; }
        thead th { background: var(--pink-100); color: var(--text); border-bottom: 2px solid var(--pink-300); }
        tbody tr:nth-child(even) { background: var(--pink-50); }
        tfoot th { background: var(--head-bg); }

        .payment {
            margin-top: 20px; padding: 12px 16px;
            background: var(--pink-50); border: 1px solid var(--pink-300);
            border-radius: 10px; font-size: 14px;
            display: flex; gap: 20px; align-items: flex-start; justify-content: space-between;
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
        <a href="{{ route('orders.show', $order) }}">&larr; Kembali ke order</a>
        <a href="{{ route('customers.invoice', $order->customer) }}">Rekap semua order customer ini</a>
    </div>

    <div class="header">
        <div>
            <h1>{{ config('shop.name') }}</h1>
            <div class="title">INVOICE</div>
        </div>
        <div class="right">
            <strong>INV-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong><br>
            Tanggal: {{ $order->order_date->format('d/m/Y') }}<br>
            <span class="badge {{ $order->payment_status === 'sudah' ? '' : 'belum' }}">
                {{ $order->payment_status === 'sudah' ? 'LUNAS' : 'BELUM DIBAYAR' }}
            </span>
        </div>
    </div>

    <p>
        <strong>Kepada:</strong><br>
        {{ $order->customer->name ?? '-' }}<br>
        {{ $order->customer->phone ?? '-' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
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
                <th colspan="3">Total</th>
                <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <p>
        Status kirim: {{ $order->shipping_status === 'sudah' ? 'Sudah dikirim' : 'Belum dikirim' }}<br>
        No. resi: {{ $order->tracking_number ?? '-' }}
    </p>

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

    <p class="thanks">Terima kasih sudah belanja di {{ config('shop.name') }}.</p>
</body>
</html>
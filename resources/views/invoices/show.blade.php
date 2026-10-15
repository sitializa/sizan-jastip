<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice INV-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 720px; margin: 24px auto; padding: 0 16px; color: #111; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; }
        h1 { margin: 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 4px 10px; border: 2px solid #15803d; color: #15803d; font-weight: bold; }
        .badge.belum { border-color: #b91c1c; color: #b91c1c; }
        .actions { margin-bottom: 16px; }

        /* Hanya berlaku saat dicetak atau disimpan jadi PDF */
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
    </div>

    <div class="header">
        <div>
            <h1>Sizan Jastip</h1>
            <div>INVOICE</div>
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
        {{ $order->customer->phone }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th class="right">Qty</th>
                <th class="right">Harga</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}{{ $item->product->brand ? ' (' . $item->product->brand . ')' : '' }}</td>
                    <td class="right">{{ $item->qty }}</td>
                    <td class="right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="right">Total</th>
                <th class="right">Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <p>
        Status kirim: {{ $order->shipping_status === 'sudah' ? 'Sudah dikirim' : 'Belum dikirim' }}<br>
        No. resi: {{ $order->tracking_number ?? '-' }}
    </p>

    <p style="margin-top: 32px;">Terima kasih sudah belanja di Sizan Jastip.</p>
</body>
</html>
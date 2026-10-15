<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sizan Jastip')</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 960px; margin: 20px auto; padding: 0 16px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .alert { background: #d1fae5; padding: 10px; margin-bottom: 12px; }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 10px; margin-bottom: 12px; }
        .error { color: #b91c1c; font-size: 14px; }
        label { display: block; margin-top: 10px; }
        input, select { padding: 6px; width: 100%; max-width: 360px; }
    </style>
</head>
<body>
    <h1>Sizan Jastip</h1>
    <nav>
        <a href="{{ route('products.index') }}">Produk</a> |
        <a href="{{ route('customers.index') }}">Customer</a> |
        <a href="{{ route('orders.index') }}">Order</a> |
        <a href="{{ route('suppliers.index') }}">Supplier</a> |
        <a href="{{ route('purchases.index') }}">Pembelian</a> |
        <a href="{{ route('expired.index') }}">Expired</a>
        | <a href="{{ route('reports.profit') }}">Laporan</a>
    </nav>
    <hr>

    @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @yield('content')
</body>
</html>
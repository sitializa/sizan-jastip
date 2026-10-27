<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sizan Jastip')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <h1>Sizan Jastip</h1>
    <nav class="menu">
        <a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.*')])>Produk</a>
        <a href="{{ route('recap.products') }}" @class(['active' => request()->routeIs('recap.*')])>Rekap</a>
        <a href="{{ route('customers.index') }}" @class(['active' => request()->routeIs('customers.*')])>Customer</a>
        <a href="{{ route('orders.index') }}" @class(['active' => request()->routeIs('orders.*')])>Order</a>
        <a href="{{ route('archive.index') }}" @class(['active' => request()->routeIs('archive.*')])>Arsip</a>
        <a href="{{ route('suppliers.index') }}" @class(['active' => request()->routeIs('suppliers.*')])>Supplier</a>
        <a href="{{ route('purchases.index') }}" @class(['active' => request()->routeIs('purchases.*')])>Pembelian</a>
        <a href="{{ route('expired.index') }}" @class(['active' => request()->routeIs('expired.*')])>Expired</a>
        <a href="{{ route('sales.daily') }}" @class(['active' => request()->routeIs('sales.*')])>Penjualan</a>
        <a href="{{ route('reports.profit') }}" @class(['active' => request()->routeIs('reports.*')])>Laporan</a>
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
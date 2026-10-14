@extends('layouts.app')

@section('title', 'Buat Order')

@section('content')
    <h2>Buat Order</h2>

    @if ($errors->any())
        <div class="error">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <label>Customer
            <select name="customer_id">
                <option value="">-- pilih --</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                        {{ $c->phone }}{{ $c->name ? ' - ' . $c->name : '' }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>Tanggal order
            <input type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}">
        </label>

        <h3>Produk</h3>
        <div id="rows">
            @foreach (old('items', [['product_id' => '', 'qty' => 1]]) as $i => $item)
                <div class="item-row" style="margin-bottom: 8px;">
                    <select name="items[{{ $i }}][product_id]" style="width:auto;">
                        <option value="">-- pilih produk --</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected(($item['product_id'] ?? '') == $p->id)>
                                {{ $p->name }} (stok {{ $p->stock }}, Rp {{ number_format($p->sell_price, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="items[{{ $i }}][qty]" value="{{ $item['qty'] ?? 1 }}" min="1" style="width:80px;">
                    <button type="button" onclick="this.parentElement.remove()">Hapus baris</button>
                </div>
            @endforeach
        </div>

        <button type="button" id="add-row">+ Tambah produk</button>

        <template id="row-template">
            <div class="item-row" style="margin-bottom: 8px;">
                <select name="items[__INDEX__][product_id]" style="width:auto;">
                    <option value="">-- pilih produk --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->name }} (stok {{ $p->stock }}, Rp {{ number_format($p->sell_price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                <input type="number" name="items[__INDEX__][qty]" value="1" min="1" style="width:80px;">
                <button type="button" onclick="this.parentElement.remove()">Hapus baris</button>
            </div>
        </template>

        <br><br>
        <button type="submit">Simpan Order</button>
        <a href="{{ route('orders.index') }}">Batal</a>
    </form>

    <script>
        let index = document.querySelectorAll('.item-row').length;

        document.getElementById('add-row').addEventListener('click', function () {
            const html = document.getElementById('row-template').innerHTML.replaceAll('__INDEX__', index++);
            document.getElementById('rows').insertAdjacentHTML('beforeend', html);
        });
    </script>
@endsection
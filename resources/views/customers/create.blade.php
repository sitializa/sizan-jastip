@extends('layouts.app')

@section('title', 'Catat Pembelian')

@section('content')
    <h2>Catat Pembelian</h2>

    @if ($errors->any())
        <div class="error">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('purchases.store') }}" method="POST">
        @csrf

        <label>Supplier
            <select name="supplier_id">
                <option value="">-- pilih --</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </label>

        <label>Tanggal pembelian
            <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}">
        </label>

        <label>Biaya tambahan (ongkir, packing, dll. untuk seluruh pembelian ini)
            <input type="number" name="extra_cost" value="{{ old('extra_cost', 0) }}" min="0">
        </label>

        <h3>Produk yang dibeli (harga beli per pcs)</h3>
        <div id="rows">
            @foreach (old('items', [['product_id' => '', 'qty' => 1, 'price' => '']]) as $i => $item)
                <div class="item-row" style="margin-bottom: 8px;">
                    <select name="items[{{ $i }}][product_id]" style="width:auto;">
                        <option value="">-- pilih produk --</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected(($item['product_id'] ?? '') == $p->id)>
                                {{ $p->name }} (stok {{ $p->stock }})
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="items[{{ $i }}][qty]" value="{{ $item['qty'] ?? 1 }}" min="1" style="width:80px;" placeholder="Qty">
                    <input type="number" name="items[{{ $i }}][price]" value="{{ $item['price'] ?? '' }}" min="1" style="width:140px;" placeholder="Harga beli">
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
                        <option value="{{ $p->id }}">{{ $p->name }} (stok {{ $p->stock }})</option>
                    @endforeach
                </select>
                <input type="number" name="items[__INDEX__][qty]" value="1" min="1" style="width:80px;" placeholder="Qty">
                <input type="number" name="items[__INDEX__][price]" min="1" style="width:140px;" placeholder="Harga beli">
                <button type="button" onclick="this.parentElement.remove()">Hapus baris</button>
            </div>
        </template>

        <br><br>
        <button type="submit">Simpan Pembelian</button>
        <a href="{{ route('purchases.index') }}">Batal</a>
    </form>

    <script>
        let index = document.querySelectorAll('.item-row').length;

        document.getElementById('add-row').addEventListener('click', function () {
            const html = document.getElementById('row-template').innerHTML.replaceAll('__INDEX__', index++);
            document.getElementById('rows').insertAdjacentHTML('beforeend', html);
        });
    </script>
@endsection
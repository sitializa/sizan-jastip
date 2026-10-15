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
                @include('purchases._row', ['i' => $i, 'item' => $item])
            @endforeach
        </div>

        <button type="button" id="add-row">+ Tambah baris produk</button>

        <template id="row-template">
            @include('purchases._row', ['i' => '__INDEX__', 'item' => []])
        </template>

        <br><br>
        <button type="submit">Simpan Pembelian</button>
        <a href="{{ route('purchases.index') }}">Batal</a>
    </form>

    <script>
        // Mulai dari angka setelah indeks terbesar yang sudah ada, supaya tidak bentrok.
        let index = {{ collect(old('items', [[]]))->keys()->max() + 1 }};

        document.getElementById('add-row').addEventListener('click', function () {
            const html = document.getElementById('row-template').innerHTML.replaceAll('__INDEX__', index++);
            document.getElementById('rows').insertAdjacentHTML('beforeend', html);
        });

        // Tampilkan isian produk baru hanya kalau "+ Produk baru..." dipilih.
        function toggleNew(select) {
            const box = select.closest('.item-row').querySelector('.new-fields');
            box.style.display = select.value === 'new' ? 'block' : 'none';
        }
    </script>
@endsection
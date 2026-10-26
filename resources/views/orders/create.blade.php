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

    <style>
        .combo { position: relative; display: inline-block; }
        .combo-input { width: 420px; max-width: 100%; }
        .combo-list {
            display: none; position: absolute; z-index: 10; left: 0; top: 100%;
            width: 420px; max-width: 90vw; max-height: 260px; overflow-y: auto;
            background: #fff; border: 1px solid #cfc2c9; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .combo-item { padding: 8px 10px; cursor: pointer; }
        .combo-item:hover { background: #fdeef4; }
        .combo-empty { padding: 8px 10px; color: #7d6b74; }
        .item-row { margin-bottom: 8px; }
    </style>

    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <label>Customer
            <select name="customer_id" id="customer-select">
                <option value="">-- pilih --</option>
                <option value="new" @selected(old('customer_id') === 'new')>+ Customer baru</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                        {{ $c->name ?? '-' }}{{ $c->phone ? ' (' . $c->phone . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </label>

        <div id="new-customer" style="display: {{ old('customer_id') === 'new' ? 'block' : 'none' }};">
            <label>Nama customer baru
                <input type="text" name="new_customer_name" value="{{ old('new_customer_name') }}" placeholder="Nama">
            </label>
            <label>No. HP (boleh kosong)
                <input type="text" name="new_customer_phone" value="{{ old('new_customer_phone') }}" placeholder="08xxxxxxxxxx">
            </label>
        </div>

        <label>Tanggal order
            <input type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}">
        </label>

        <h3>Produk</h3>
        <div id="rows"></div>

        <button type="button" id="add-row">+ Tambah produk</button>

        <br><br>
        <button type="submit">Simpan Order</button>
        <a href="{{ route('orders.index') }}">Batal</a>
    </form>

    <script>
        // Daftar produk dikirim dari server (hanya yang stoknya ada dan harga jualnya terisi).
        const products = @json($products->map(fn ($p) => [
            'id'    => $p->id,
            'label' => $p->name . ' (stok ' . $p->stock . ', Rp ' . number_format($p->sell_price, 0, ',', '.') . ')',
        ])->values());

        // Baris awal: isian lama kalau form ditolak, atau satu baris kosong.
        const initialRows = @json(array_values(old('items', [['product_id' => '', 'qty' => 1]])));

        const rows = document.getElementById('rows');
        let index = 0;

        function labelOf(id) {
            const p = products.find(p => String(p.id) === String(id));
            return p ? p.label : '';
        }

        function addRow(productId, qty) {
            const i = index++;
            const div = document.createElement('div');
            div.className = 'item-row';
            div.innerHTML = `
                <div class="combo">
                    <input type="text" class="combo-input" placeholder="Ketik untuk cari produk..." autocomplete="off">
                    <input type="hidden" class="combo-value" name="items[${i}][product_id]">
                    <div class="combo-list"></div>
                </div>
                <input type="number" name="items[${i}][qty]" min="1" style="width:80px;">
                <button type="button" class="remove-row">Hapus baris</button>`;
            rows.appendChild(div);

            div.querySelector('input[type=number]').value = qty || 1;
            div.querySelector('.combo-value').value = productId || '';
            div.querySelector('.combo-input').value = labelOf(productId);
        }

        // Tampilkan hasil saringan: semua kata yang diketik harus ada di nama produk.
        function renderList(combo, term) {
            const list = combo.querySelector('.combo-list');
            const words = term.toLowerCase().split(/\s+/).filter(Boolean);
            const found = products
                .filter(p => words.every(w => p.label.toLowerCase().includes(w)))
                .slice(0, 50);

            list.innerHTML = '';

            if (found.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'combo-empty';
                empty.textContent = 'Tidak ada produk yang cocok';
                list.appendChild(empty);
            }

            found.forEach(p => {
                const item = document.createElement('div');
                item.className = 'combo-item';
                item.dataset.id = p.id;
                item.textContent = p.label;
                list.appendChild(item);
            });

            list.style.display = 'block';
        }

        function choose(combo, id) {
            combo.querySelector('.combo-value').value = id;
            combo.querySelector('.combo-input').value = labelOf(id);
            combo.querySelector('.combo-list').style.display = 'none';
        }

        // Mengetik: kosongkan pilihan lama, lalu saring ulang.
        rows.addEventListener('input', e => {
            if (!e.target.classList.contains('combo-input')) return;
            const combo = e.target.closest('.combo');
            combo.querySelector('.combo-value').value = '';
            renderList(combo, e.target.value);
        });

        // Klik kolom: tampilkan daftar (isi saringannya = teks yang sudah ada).
        rows.addEventListener('focusin', e => {
            if (e.target.classList.contains('combo-input')) {
                renderList(e.target.closest('.combo'), e.target.value);
            }
        });

        // Enter memilih hasil teratas, bukan mengirim form.
        rows.addEventListener('keydown', e => {
            if (e.key === 'Enter' && e.target.classList.contains('combo-input')) {
                e.preventDefault();
                const combo = e.target.closest('.combo');
                const first = combo.querySelector('.combo-item');
                if (first) choose(combo, first.dataset.id);
            }
        });

        rows.addEventListener('click', e => {
            if (e.target.classList.contains('combo-item')) {
                choose(e.target.closest('.combo'), e.target.dataset.id);
            }
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('.item-row').remove();
            }
        });

        // Klik di luar kolom: tutup daftar.
        document.addEventListener('click', e => {
            if (!e.target.closest('.combo')) {
                document.querySelectorAll('.combo-list').forEach(l => l.style.display = 'none');
            }
        });

        document.getElementById('add-row').addEventListener('click', () => addRow('', 1));
        initialRows.forEach(r => addRow(r.product_id, r.qty));

        // Isian customer baru hanya tampil kalau "+ Customer baru" dipilih.
        document.getElementById('customer-select').addEventListener('change', function () {
            document.getElementById('new-customer').style.display = this.value === 'new' ? 'block' : 'none';
        });
    </script>
@endsection
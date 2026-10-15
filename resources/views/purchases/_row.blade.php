@php
    $isNew = ($item['product_id'] ?? '') === 'new';
@endphp

<div class="item-row" style="border: 1px solid #ddd; padding: 8px; margin-bottom: 8px;">
    <select name="items[{{ $i }}][product_id]" style="width:auto;" onchange="toggleNew(this)">
        <option value="">-- pilih produk --</option>
        <option value="new" @selected($isNew)>+ Produk baru...</option>
        @foreach ($products as $p)
            <option value="{{ $p->id }}" @selected(($item['product_id'] ?? '') == $p->id)>
                {{ $p->name }} (stok {{ $p->stock }})
            </option>
        @endforeach
    </select>
    <input type="number" name="items[{{ $i }}][qty]" value="{{ $item['qty'] ?? 1 }}" min="1" style="width:80px;" placeholder="Qty">
    <input type="number" name="items[{{ $i }}][price]" value="{{ $item['price'] ?? '' }}" min="1" style="width:140px;" placeholder="Harga beli">
    <button type="button" onclick="this.closest('.item-row').remove()">Hapus baris</button>

    <div class="new-fields" style="margin-top: 8px; {{ $isNew ? '' : 'display: none;' }}">
        <div style="font-size: 14px; margin-bottom: 6px;">
            Data produk baru (otomatis masuk ke daftar Produk dengan stok = qty yang dibeli).
            Harga jual diisi nanti di halaman Produk.
        </div>
        <input type="text" name="items[{{ $i }}][new_name]" value="{{ $item['new_name'] ?? '' }}" style="width:200px;" placeholder="Nama produk baru">
        <input type="text" name="items[{{ $i }}][new_brand]" value="{{ $item['new_brand'] ?? '' }}" style="width:140px;" placeholder="Brand (opsional)">
        <select name="items[{{ $i }}][new_category]" style="width:auto;">
            <option value="">-- kategori --</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected(($item['new_category'] ?? '') === $cat)>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>
        <label style="display: inline; margin: 0;">Exp:
            <input type="date" name="items[{{ $i }}][new_expired_date]" value="{{ $item['new_expired_date'] ?? '' }}" style="width:auto;">
        </label>
    </div>
</div>
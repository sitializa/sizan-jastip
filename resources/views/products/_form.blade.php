@csrf

<label>Nama
    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}">
</label>
@error('name') <div class="error">{{ $message }}</div> @enderror

<label>Brand (boleh kosong)
    <input type="text" name="brand" value="{{ old('brand', $product->brand ?? '') }}">
</label>
@error('brand') <div class="error">{{ $message }}</div> @enderror

<label>Kategori
    <select name="category">
        <option value="">-- pilih --</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat }}" @selected(old('category', $product->category ?? '') === $cat)>{{ ucfirst($cat) }}</option>
        @endforeach
    </select>
</label>
@error('category') <div class="error">{{ $message }}</div> @enderror

<label>Harga modal (wajib)
    <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}">
</label>
@error('cost_price') <div class="error">{{ $message }}</div> @enderror

<label>Harga jual
    <input type="number" name="sell_price" value="{{ old('sell_price', $product->sell_price ?? '') }}">
</label>
@error('sell_price') <div class="error">{{ $message }}</div> @enderror

<label>Stok
    <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}">
</label>
@error('stock') <div class="error">{{ $message }}</div> @enderror

<label>Tanggal expired (boleh kosong)
    <input type="date" name="expired_date" value="{{ old('expired_date', isset($product) ? $product->expired_date?->format('Y-m-d') : '') }}">
</label>
@error('expired_date') <div class="error">{{ $message }}</div> @enderror

{{-- Hidden 0 memastikan nilai tetap terkirim (false) saat kotak tidak dicentang --}}
<input type="hidden" name="needs_check" value="0">
<label style="display: flex; align-items: center; gap: 8px;">
    <input type="checkbox" name="needs_check" value="1" style="width: auto;"
           @checked(old('needs_check', $product->needs_check ?? false))>
    ⚠ Cek lagi nanti
</label>

<br>
<button type="submit">Simpan</button>
<a href="{{ route('products.index') }}">Batal</a>
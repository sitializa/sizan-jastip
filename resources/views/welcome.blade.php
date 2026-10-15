@csrf

<label>Nama supplier / toko
    <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}">
</label>
@error('name') <div class="error">{{ $message }}</div> @enderror

<label>Kontak (boleh kosong)
    <input type="text" name="contact" value="{{ old('contact', $supplier->contact ?? '') }}">
</label>
@error('contact') <div class="error">{{ $message }}</div> @enderror

<br>
<button type="submit">Simpan</button>
<a href="{{ route('suppliers.index') }}">Batal</a>
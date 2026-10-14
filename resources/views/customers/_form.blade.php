@csrf

<label>No. HP
    <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}">
</label>
@error('phone') <div class="error">{{ $message }}</div> @enderror

<label>Nama (boleh kosong)
    <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}">
</label>
@error('name') <div class="error">{{ $message }}</div> @enderror

<br>
<button type="submit">Simpan</button>
<a href="{{ route('customers.index') }}">Batal</a>
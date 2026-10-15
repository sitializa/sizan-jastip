@extends('layouts.app')

@section('title', 'Daftar Supplier')

@section('content')
    <h2>Daftar Supplier</h2>
    <a href="{{ route('suppliers.create') }}">+ Tambah Supplier</a>

    <table style="margin-top: 12px;">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kontak</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->contact ?? '-' }}</td>
                    <td>
                        <a href="{{ route('suppliers.edit', $supplier) }}">Ubah</a>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus supplier ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada supplier.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $suppliers->links() }}</div>
@endsection
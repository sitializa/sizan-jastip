@extends('layouts.app')

@section('title', 'Daftar Customer')

@section('content')
    <h2>Daftar Customer</h2>
    <a href="{{ route('customers.create') }}">+ Tambah Customer</a>

    <table style="margin-top: 12px;">
        <thead>
            <tr>
                <th>No. HP</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>{{ $customer->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('customers.edit', $customer) }}">Ubah</a>
                        <a href="{{ route('customers.invoice', $customer) }}" target="_blank">Invoice</a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus customer ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada customer.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">{{ $customers->links() }}</div>
@endsection
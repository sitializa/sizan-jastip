@extends('layouts.app')

@section('title', 'Ubah Supplier')

@section('content')
    <h2>Ubah Supplier</h2>
    <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
        @method('PUT')
        @include('suppliers._form')
    </form>
@endsection
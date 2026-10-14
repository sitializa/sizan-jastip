@extends('layouts.app')

@section('title', 'Ubah Produk')

@section('content')
    <h2>Ubah Produk</h2>
    <form action="{{ route('products.update', $product) }}" method="POST">
        @method('PUT')
        @include('products._form')
    </form>
@endsection
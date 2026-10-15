@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
    <h2>Tambah Supplier</h2>
    <form action="{{ route('suppliers.store') }}" method="POST">
        @include('suppliers._form')
    </form>
@endsection
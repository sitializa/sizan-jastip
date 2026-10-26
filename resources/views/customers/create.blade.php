@extends('layouts.app')

@section('title', 'Tambah Customer')

@section('content')
    <h2>Tambah Customer</h2>
    <form action="{{ route('customers.store') }}" method="POST">
        @include('customers._form')
    </form>
@endsection
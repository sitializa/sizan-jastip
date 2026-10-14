@extends('layouts.app')

@section('title', 'Ubah Customer')

@section('content')
    <h2>Ubah Customer</h2>
    <form action="{{ route('customers.update', $customer) }}" method="POST">
        @method('PUT')
        @include('customers._form')
    </form>
@endsection
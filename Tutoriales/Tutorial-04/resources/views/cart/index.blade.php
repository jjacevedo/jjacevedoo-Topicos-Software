@extends('layouts.app')

@section('title', 'Cart')

@section('subtitle', 'Shopping Cart')

@section('content')

    <h3>Products</h3>

    @foreach ($products as $product)
        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ $product['name'] }}</h5>

                <p>Price: {{ $product['price'] }}</p>

                <a href="{{ route('cart.add', $product['id']) }}"
                   class="btn btn-primary">
                    Add to cart
                </a>
            </div>
        </div>
    @endforeach

    <hr>

    <h3>Cart</h3>

    @if ($cartProductData)
        @foreach ($cartProductData as $product)
            <div class="card mb-3">
                <div class="card-body">
                    <h5>{{ $product['name'] }}</h5>
                    <p>Price: {{ $product['price'] }}</p>
                </div>
            </div>
        @endforeach

        <a href="{{ route('cart.removeAll') }}"
           class="btn btn-danger">
            Remove all products
        </a>
    @else
        <p>The cart is empty.</p>
    @endif

@endsection
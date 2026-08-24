@extends('layouts.app')

@section('title', $viewData["title"])

@section('subtitle', $viewData["subtitle"])

@section('content')

<div class="container">

    <div class="row">

        @foreach ($viewData["products"] as $product)

            <div class="col-md-4 mb-4">

                <div class="card">

                    <div class="card-body">

                        <h5 class="card-title">
                            {{ $product["name"] }}
                        </h5>

                        <p class="card-text">
                            ${{ $product["price"] }}
                        </p>

                        <a href="{{ route('product.show', ['id' => $product['id']]) }}"
                           class="btn btn-primary">
                            View product
                        </a>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endsection
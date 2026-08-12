@extends('layouts.app')

@section('title', $viewData["title"])

@section('subtitle', $viewData["subtitle"])

@section('content')

<div class="container">

    <div class="row">

        <div class="col-md-6">

            <img src="{{ asset('/img/' . $viewData["product"]["image"]) }}"
                 class="img-fluid"
                 alt="{{ $viewData["product"]["name"] }}">

        </div>

        <div class="col-md-6">

            <h2>{{ $viewData["product"]["name"] }}</h2>

            <h4>${{ $viewData["product"]["price"] }}</h4>

            <p>
                {{ $viewData["product"]["description"] }}
            </p>

        </div>

    </div>

</div>

@endsection

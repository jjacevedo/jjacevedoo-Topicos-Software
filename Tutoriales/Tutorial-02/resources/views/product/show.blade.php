@extends('layouts.app')

@section('title', $viewData["title"])

@section('subtitle', $viewData["subtitle"])

@section('content')

<div class="container">

    <div class="row">

        <div class="col-md-6">

            <div class="card mb-3">
                <div class="card-body">

                    <h2>{{ $viewData["product"]["name"] }}</h2>
                    <h4>${{ $viewData["product"]["price"] }}</h4>

                </div>
            </div>

            <div class="card">

                <div class="card-header">
                    Add Comment
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('product.comment.store') }}">

                        @csrf

                        <input
                            type="hidden"
                            name="product_id"
                            value="{{ $viewData["product"]["id"] }}">

                        <div class="mb-3">
                            <label class="form-label">Comment</label>

                            <textarea
                                class="form-control"
                                name="description"
                                rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Submit Comment
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <h3>Comments</h3>

            @foreach ($viewData["product"]->comments as $comment)

                <div class="card mb-3">

                    <div class="card-body">

                        {{ $comment->description }}

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

@endsection
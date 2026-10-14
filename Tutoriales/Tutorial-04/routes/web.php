<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', 'App\Http\Controllers\HomeController@index')
    ->name('home.index');

Route::get('/about', function () {
    $data1 = 'About us - Online Store';
    $data2 = 'About us';
    $description = 'This is an about page ...';
    $author = 'Developed by: Juan José Acevedo';

    return view('home.about')
        ->with('title', $data1)
        ->with('subtitle', $data2)
        ->with('description', $description)
        ->with('author', $author);
})->name('home.about');

Route::get('/products', 'App\Http\Controllers\ProductController@index')
    ->name('product.index');

Route::get('/products/create', 'App\Http\Controllers\ProductController@create')
    ->name('product.create');

Route::post('/products/save', 'App\Http\Controllers\ProductController@save')
    ->name('product.save');

Route::get('/products/{id}', 'App\Http\Controllers\ProductController@show')
    ->name('product.show');

Route::post('/products/comment', 'App\Http\Controllers\ProductController@commentStore')
    ->name('product.comment.store');

Route::get('/cart', [CartController::class, 'index'])
    ->name('cart.index');

Route::get('/cart/add/{id}', [CartController::class, 'add'])
    ->name('cart.add');

Route::get('/cart/removeAll', [CartController::class, 'removeAll'])
    ->name('cart.removeAll');
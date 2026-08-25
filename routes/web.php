<?php

use App\Http\Controllers\VideojuegoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideojuegoController::class, 'home'])
    ->name('videojuegos.home');

Route::get('/videojuegos', [VideojuegoController::class, 'index'])
    ->name('videojuegos.index');

Route::get('/videojuegos/create', [VideojuegoController::class, 'create'])
    ->name('videojuegos.create');

Route::post('/videojuegos', [VideojuegoController::class, 'store'])
    ->name('videojuegos.store');
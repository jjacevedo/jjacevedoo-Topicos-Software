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

Route::get('/videojuegos/{id}', [VideojuegoController::class, 'show'])
    ->name('videojuegos.show');

Route::delete('/videojuegos/{id}', [VideojuegoController::class, 'destroy'])
    ->name('videojuegos.destroy');

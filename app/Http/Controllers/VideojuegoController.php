<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class VideojuegoController extends Controller
{
    public function home(): View
    {
        return view('videojuegos.home');
    }

    public function index(): View
    {
        return view('videojuegos.index');
    }

    public function create(): View
    {
        return view('videojuegos.create');
    }
}
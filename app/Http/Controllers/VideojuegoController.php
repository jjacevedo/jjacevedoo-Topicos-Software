<?php

namespace App\Http\Controllers;

use App\Models\Videojuego;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideojuegoController extends Controller
{
    public function home(): View
    {
        return view('videojuegos.home');
    }

    public function index(): View
    {
        $videojuegos = Videojuego::all();

        return view('videojuegos.index', compact('videojuegos'));
    }

    public function create(): View
    {
        return view('videojuegos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'fecha_lanzamiento' => 'required|date',
            'stock' => 'required|integer|min:0',
            'plataforma' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
        ]);

        $videojuego = new Videojuego();
        $videojuego->nombre = $validated['nombre'];
        $videojuego->precio = $validated['precio'];
        $videojuego->descripcion = $validated['descripcion'];
        $videojuego->fecha_lanzamiento = $validated['fecha_lanzamiento'];
        $videojuego->stock = $validated['stock'];
        $videojuego->plataforma = $validated['plataforma'];
        $videojuego->categoria = $validated['categoria'];
        $videojuego->save();

        return redirect()->route('videojuegos.index');
    }
}
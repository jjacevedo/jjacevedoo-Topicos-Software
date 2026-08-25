<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Videojuego extends Model
{
    protected $table = 'videojuegos';

    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'fecha_lanzamiento',
        'stock',
        'plataforma',
        'categoria',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_lanzamiento' => 'date',
        'stock' => 'integer',
    ];
}
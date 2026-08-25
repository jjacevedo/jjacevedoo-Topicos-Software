<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $videojuego->nombre }}</title>
</head>
<body>

    <h1>{{ $videojuego->nombre }}</h1>

    <p>
        <strong>ID:</strong>
        {{ $videojuego->id }}
    </p>

    <p>
        <strong>Precio:</strong>
        ${{ $videojuego->precio }}
    </p>

    <p>
        <strong>Descripción:</strong>
        {{ $videojuego->descripcion }}
    </p>

    <p>
        <strong>Fecha de lanzamiento:</strong>
        {{ $videojuego->fecha_lanzamiento->format('Y-m-d') }}
    </p>

    <p>
        <strong>Stock:</strong>
        {{ $videojuego->stock }}
    </p>

    <p>
        <strong>Plataforma:</strong>
        {{ $videojuego->plataforma }}
    </p>

    <p>
        <strong>Categoría:</strong>
        {{ $videojuego->categoria }}
    </p>

    <br>

    <a href="{{ route('videojuegos.edit', $videojuego->id) }}">
        Editar videojuego
    </a>

    <br>

    <a href="{{ route('videojuegos.index') }}">
        Volver a la lista
    </a>

</body>
</html>
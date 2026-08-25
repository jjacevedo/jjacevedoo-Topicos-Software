<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de videojuegos</title>
</head>
<body>

    <h1>Lista de videojuegos</h1>

    @if ($videojuegos->isEmpty())
        <p>No hay videojuegos registrados.</p>
    @else
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Plataforma</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($videojuegos as $videojuego)
                    <tr>
                        <td>{{ $videojuego->id }}</td>
                        <td>{{ $videojuego->nombre }}</td>
                        <td>{{ $videojuego->precio }}</td>
                        <td>{{ $videojuego->plataforma }}</td>
                        <td>{{ $videojuego->categoria }}</td>
                        <td>{{ $videojuego->stock }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <br>

    <a href="{{ route('videojuegos.create') }}">
        Crear videojuego
    </a>

    <br>

    <a href="{{ route('videojuegos.home') }}">
        Volver al inicio
    </a>

</body>
</html>
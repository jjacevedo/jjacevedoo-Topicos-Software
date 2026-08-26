<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de videojuegos</title>
</head>
<body>

    <h1>Lista de videojuegos</h1>

@if (session('success'))
    <p>{{ session('success') }}</p>
@endif

@if ($videojuegos->isEmpty())
        <p>No hay videojuegos registrados.</p>
    @else
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($videojuegos as $videojuego)
                    <tr>
                        <td>
                            <a href="{{ route('videojuegos.show', $videojuego->id) }}">
                                {{ $videojuego->id }}
                            </a>
                        </td>
                        <td>{{ $videojuego->nombre }}</td>
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
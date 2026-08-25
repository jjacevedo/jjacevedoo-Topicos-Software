<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller 01 - Videojuegos</title>
</head>
<body>

    <h1>Taller 01 - Videojuegos</h1>

    <ul>
        <li>
            <a href="{{ route('videojuegos.create') }}">
                Crear videojuego
            </a>
        </li>

        <li>
            <a href="{{ route('videojuegos.index') }}">
                Listar videojuegos
            </a>
        </li>
    </ul>

</body>
</html>
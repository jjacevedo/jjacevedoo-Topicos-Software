<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar videojuego</title>
</head>
<body>

    <h1>Editar videojuego</h1>

    <form method="POST" action="{{ route('videojuegos.update', $videojuego->id) }}">
        @csrf
        @method('PUT')

        <div>
            <label for="nombre">Nombre:</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                value="{{ old('nombre', $videojuego->nombre) }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="precio">Precio:</label>
            <input
                type="number"
                id="precio"
                name="precio"
                step="0.01"
                min="0"
                value="{{ old('precio', $videojuego->precio) }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="descripcion">Descripción:</label>
            <textarea
                id="descripcion"
                name="descripcion"
                required
            >{{ old('descripcion', $videojuego->descripcion) }}</textarea>
        </div>

        <br>

        <div>
            <label for="fecha_lanzamiento">Fecha de lanzamiento:</label>
            <input
                type="date"
                id="fecha_lanzamiento"
                name="fecha_lanzamiento"
                value="{{ old('fecha_lanzamiento', $videojuego->fecha_lanzamiento->format('Y-m-d')) }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="stock">Stock:</label>
            <input
                type="number"
                id="stock"
                name="stock"
                min="0"
                value="{{ old('stock', $videojuego->stock) }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="plataforma">Plataforma:</label>
            <input
                type="text"
                id="plataforma"
                name="plataforma"
                value="{{ old('plataforma', $videojuego->plataforma) }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="categoria">Categoría:</label>
            <input
                type="text"
                id="categoria"
                name="categoria"
                value="{{ old('categoria', $videojuego->categoria) }}"
                required
            >
        </div>

        <br>

        <button type="submit">Guardar cambios</button>
    </form>

    <br>

    <a href="{{ route('videojuegos.show', $videojuego->id) }}">
        Cancelar
    </a>

</body>
</html>
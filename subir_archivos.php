<?php

function IPCliente() {
    return $_SERVER['REMOTE_ADDR'] ?? 'Origen del Puesto desconocido.';
}

function HOSTCliente($ip) {
    return ($ip === "Origen del Puesto desconocido.") ? $ip : gethostbyaddr($ip);
}

// Inicializar variables
$errores = [];
$resultado = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑçÇ ]+$/", $nombre)) {
        $errores[] = "Nombre y apellidos no válidos.";
    }

    $curso = filter_input(INPUT_POST, 'curso', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cursos_validos = ["4ESO", "1ASI", "2ASI", "2BACH", "1DAI"];
    if (!in_array($curso, $cursos_validos)) {
        $errores[] = "Curso no válido.";
    }

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['archivo'];
        $nombre_archivo = basename($archivo['name']);
        $ruta_destino = __DIR__ . "/";
        $fecha = date('YmdHis');
        $host = HOSTCliente(IPCliente());
        $nombre_sanitizado = str_replace(' ', '_', $nombre);
        $nombre_final = sprintf("%s-%s-%s-%s-%s", $curso, $fecha, $host, $nombre_sanitizado, $nombre_archivo);

        if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino . $nombre_final)) {
            $errores[] = "Error al guardar el archivo.";
        }
    } else {
        $errores[] = "Error en el archivo enviado.";
    }

    if (empty($errores)) {
        $resultado = "<h2>Datos tratados correctamente</h2>
                      <p><strong>Nombre:</strong> $nombre</p>
                      <p><strong>Curso:</strong> $curso</p>
                      <p><strong>Archivo guardado como:</strong> $nombre_final</p>";
    } else {
        $resultado = "<h2>Errores encontrados:</h2><ul>";
        foreach ($errores as $error) {
            $resultado .= "<li>$error</li>";
        }
        $resultado .= "</ul>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subida de Archivos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
            <label for="nombre">Nombre y Apellidos:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="curso">Curso:</label>
            <select id="curso" name="curso" required>
                <option value="4ESO">4ESO</option>
                <option value="1ASI">1ASI</option>
                <option value="2ASI">2ASI</option>
                <option value="2BACH">2BACH</option>
                <option value="1DAI">1DAI</option>
            </select>

            <label for="archivo">Archivo:</label>
            <input type="file" id="archivo" name="archivo" required>

            <button type="submit">Enviar</button>
        </form>

        <?php if (!empty($resultado)): ?>
            <div class="result">
                <?php echo $resultado; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

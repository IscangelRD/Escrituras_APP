<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellidoPaterno = trim($_POST['apellido_paterno'] ?? '');
    $apellidoMaterno = trim($_POST['apellido_materno'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $nombre === '' ||
        $apellidoPaterno === '' ||
        $correo === '' ||
        $usuario === '' ||
        $password === ''
    ) {

        $mensaje = 'Completa los campos obligatorios.';

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $mensaje = 'El correo electrónico no es válido.';

    } elseif (strlen($password) < 8) {

        $mensaje = 'La contraseña debe tener al menos 8 caracteres.';

    } else {

        try {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "
                INSERT INTO usuarios (
                    nombre,
                    apellido_paterno,
                    apellido_materno,
                    correo,
                    telefono,
                    usuario,
                    password_hash,
                    rol_id,
                    activo
                )
                VALUES (
                    :nombre,
                    :apellido_paterno,
                    :apellido_materno,
                    :correo,
                    :telefono,
                    :usuario,
                    :password_hash,
                    1,
                    1
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':nombre' => $nombre,
                ':apellido_paterno' => $apellidoPaterno,
                ':apellido_materno' => $apellidoMaterno !== ''
                    ? $apellidoMaterno
                    : null,
                ':correo' => $correo,
                ':telefono' => $telefono !== ''
                    ? $telefono
                    : null,
                ':usuario' => $usuario,
                ':password_hash' => $passwordHash
            ]);

            $mensaje = 'Administrador creado correctamente.';

        } catch (PDOException $e) {

            if ((int)$e->errorInfo[1] === 1062) {

                $mensaje = 'El usuario o correo ya existe.';

            } else {

                error_log($e->getMessage());

                $mensaje = 'No fue posible crear el administrador.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Crear administrador</title>

</head>

<body>

<h1>Crear administrador</h1>

<?php if ($mensaje !== ''): ?>

    <p>
        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
    </p>

<?php endif; ?>

<form method="POST">

    <label>
        Nombre
        <input
            type="text"
            name="nombre"
            required
        >
    </label>

    <br><br>

    <label>
        Apellido paterno
        <input
            type="text"
            name="apellido_paterno"
            required
        >
    </label>

    <br><br>

    <label>
        Apellido materno
        <input
            type="text"
            name="apellido_materno"
        >
    </label>

    <br><br>

    <label>
        Correo
        <input
            type="email"
            name="correo"
            required
        >
    </label>

    <br><br>

    <label>
        Teléfono
        <input
            type="text"
            name="telefono"
        >
    </label>

    <br><br>

    <label>
        Usuario
        <input
            type="text"
            name="usuario"
            required
        >
    </label>

    <br><br>

    <label>
        Contraseña
        <input
            type="password"
            name="password"
            required
            minlength="8"
        >
    </label>

    <br><br>

    <button type="submit">
        Crear administrador
    </button>

</form>

</body>

</html>

<?php

//Aguador
//Angelalonso45#
?>
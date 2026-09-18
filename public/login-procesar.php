<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: login.php');

    exit;
}

if (!validarCsrf($_POST['csrf_token'] ?? null)) {

    $_SESSION['login_error'] =
        'La sesión del formulario expiró. Intenta nuevamente.';

    header('Location: login.php');

    exit;
}

$identificador =
    trim($_POST['identificador'] ?? '');

$password =
    $_POST['password'] ?? '';

if (
    $identificador === '' ||
    $password === ''
) {

    $_SESSION['login_error'] =
        'Ingresa tu usuario y contraseña.';

    header('Location: login.php');

    exit;
}

/*
|--------------------------------------------------------------------------
| Buscar usuario
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.id,
        u.nombre,
        u.apellido_paterno,
        u.apellido_materno,
        u.usuario,
        u.correo,
        u.password_hash,
        u.rol_id,
        u.activo,
        r.nombre AS rol_nombre,
        r.activo AS rol_activo
    FROM usuarios u
    INNER JOIN roles r
        ON r.id = u.rol_id
    WHERE
        (
            u.usuario = :usuario
            OR
            u.correo = :correo
        )
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':usuario' => $identificador,
    ':correo' => $identificador
]);

$usuario = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| Usuario inexistente
|--------------------------------------------------------------------------
*/

if (!$usuario) {

    registrarIntentoLogin(
        $pdo,
        null,
        $identificador,
        false
    );

    $_SESSION['login_error'] =
        'Usuario o contraseña incorrectos.';

    header('Location: login.php');

    exit;
}

/*
|--------------------------------------------------------------------------
| Usuario o rol inactivo
|--------------------------------------------------------------------------
*/

if (
    (int)$usuario['activo'] !== 1 ||
    (int)$usuario['rol_activo'] !== 1
) {

    registrarIntentoLogin(
        $pdo,
        (int)$usuario['id'],
        $identificador,
        false
    );

    $_SESSION['login_error'] =
        'La cuenta no se encuentra disponible.';

    header('Location: login.php');

    exit;
}

/*
|--------------------------------------------------------------------------
| Comprobar contraseña
|--------------------------------------------------------------------------
*/

if (!password_verify(
    $password,
    $usuario['password_hash']
)) {

    registrarIntentoLogin(
        $pdo,
        (int)$usuario['id'],
        $identificador,
        false
    );

    $_SESSION['login_error'] =
        'Usuario o contraseña incorrectos.';

    header('Location: login.php');

    exit;
}

/*
|--------------------------------------------------------------------------
| Login correcto
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

$_SESSION['usuario_id'] =
    (int)$usuario['id'];

$_SESSION['rol_id'] =
    (int)$usuario['rol_id'];

$_SESSION['usuario_nombre'] =
    trim(
        $usuario['nombre'] . ' ' .
        $usuario['apellido_paterno']
    );

$_SESSION['usuario'] =
    $usuario['usuario'];

$_SESSION['rol_nombre'] =
    $usuario['rol_nombre'];

unset($_SESSION['csrf_token']);

/*
|--------------------------------------------------------------------------
| Actualizar último acceso
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE usuarios
    SET ultimo_acceso = NOW()
    WHERE id = :id
");

$stmt->execute([
    ':id' => $usuario['id']
]);

/*
|--------------------------------------------------------------------------
| Registrar intento exitoso
|--------------------------------------------------------------------------
*/

registrarIntentoLogin(
    $pdo,
    (int)$usuario['id'],
    $identificador,
    true
);

header('Location: index.php');

exit;


/*
|--------------------------------------------------------------------------
| Función
|--------------------------------------------------------------------------
*/

function registrarIntentoLogin(
    PDO $pdo,
    ?int $usuarioId,
    string $identificador,
    bool $exitoso
): void {

    $stmt = $pdo->prepare("
        INSERT INTO intentos_login (
            usuario_id,
            usuario_intentado,
            exitoso,
            ip,
            user_agent
        )
        VALUES (
            :usuario_id,
            :usuario_intentado,
            :exitoso,
            :ip,
            :user_agent
        )
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId,

        ':usuario_intentado' =>
            mb_substr(
                $identificador,
                0,
                100
            ),

        ':exitoso' =>
            $exitoso ? 1 : 0,

        ':ip' =>
            $_SERVER['REMOTE_ADDR'] ?? null,

        ':user_agent' =>
            isset($_SERVER['HTTP_USER_AGENT'])
                ? mb_substr(
                    $_SERVER['HTTP_USER_AGENT'],
                    0,
                    500
                )
                : null
    ]);
}
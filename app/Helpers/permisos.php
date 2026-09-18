<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| Comprobar permiso
|--------------------------------------------------------------------------
*/

function tienePermiso(
    PDO $pdo,
    string $modulo,
    string $accion
): bool {

    $rolId = rolId();

    if (!$rolId) {
        return false;
    }

    $sql = "
        SELECT COUNT(*)
        FROM rol_permisos rp

        INNER JOIN permisos p
            ON p.id = rp.permiso_id

        INNER JOIN roles r
            ON r.id = rp.rol_id

        WHERE
            rp.rol_id = :rol_id
            AND p.modulo = :modulo
            AND p.accion = :accion
            AND r.activo = 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':rol_id' => $rolId,
        ':modulo' => $modulo,
        ':accion' => $accion
    ]);

    return (int) $stmt->fetchColumn() > 0;
}


/*
|--------------------------------------------------------------------------
| Exigir permiso
|--------------------------------------------------------------------------
*/

function exigirPermiso(
    PDO $pdo,
    string $modulo,
    string $accion
): void {

    exigirAutenticacion();

    if (!tienePermiso(
        $pdo,
        $modulo,
        $accion
    )) {

        http_response_code(403);

        exit('
            <!DOCTYPE html>

            <html lang="es">

            <head>

                <meta charset="UTF-8">

                <meta
                    name="viewport"
                    content="width=device-width, initial-scale=1.0"
                >

                <title>
                    Acceso denegado
                </title>

                <style>

                    body {
                        margin: 0;
                        min-height: 100vh;

                        display: flex;
                        align-items: center;
                        justify-content: center;

                        font-family:
                            Arial,
                            Helvetica,
                            sans-serif;

                        background: #f3f4f6;
                    }

                    .error {
                        width: 90%;
                        max-width: 420px;

                        padding: 30px;

                        text-align: center;

                        background: #ffffff;

                        border-radius: 16px;

                        box-shadow:
                            0 10px 30px
                            rgba(0,0,0,.10);
                    }

                    .error h1 {
                        margin: 0 0 10px;

                        font-size: 48px;

                        color: #991b1b;
                    }

                    .error h2 {
                        margin: 0 0 10px;

                        font-size: 20px;
                    }

                    .error p {
                        color: #6b7280;

                        line-height: 1.5;
                    }

                    .error a {
                        display: inline-block;

                        margin-top: 15px;

                        padding: 11px 18px;

                        border-radius: 9px;

                        background: #111827;

                        color: #ffffff;

                        text-decoration: none;
                    }

                </style>

            </head>

            <body>

                <div class="error">

                    <h1>403</h1>

                    <h2>
                        Acceso denegado
                    </h2>

                    <p>
                        No tienes permiso para
                        realizar esta acción.
                    </p>

                    <a href="index.php">
                        Volver al Dashboard
                    </a>

                </div>

            </body>

            </html>
        ');
    }
}
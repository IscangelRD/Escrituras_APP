<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/Helpers/permisos.php';

exigirPermiso(
    $pdo,
    'expedientes',
    'crear'
);


/*
|--------------------------------------------------------------------------
| Funciones auxiliares
|--------------------------------------------------------------------------
*/

function obtenerUsuarioActual(): int
{
    if (function_exists('usuarioId')) {

        $id = usuarioId();

        if ($id) {
            return (int) $id;
        }
    }


    $posiblesIds = [
        'usuario_id',
        'user_id',
        'id_usuario',
        'id'
    ];


    foreach ($posiblesIds as $clave) {

        if (
            isset($_SESSION[$clave])
            && (int) $_SESSION[$clave] > 0
        ) {

            return (int) $_SESSION[$clave];

        }
    }


    throw new RuntimeException(
        'No se pudo identificar al usuario actual.'
    );
}


/*
|--------------------------------------------------------------------------
| Datos enviados
|--------------------------------------------------------------------------
*/

$tipoTramiteId = filter_input(
    INPUT_POST,
    'tipo_tramite_id',
    FILTER_VALIDATE_INT
);

$estadoId = filter_input(
    INPUT_POST,
    'estado_id',
    FILTER_VALIDATE_INT
);

$etapaActualId = filter_input(
    INPUT_POST,
    'etapa_actual_id',
    FILTER_VALIDATE_INT
);

$clienteId = filter_input(
    INPUT_POST,
    'cliente_id',
    FILTER_VALIDATE_INT
);

$gestorId = filter_input(
    INPUT_POST,
    'gestor_id',
    FILTER_VALIDATE_INT
);

$observaciones = trim(
    $_POST['observaciones'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validaciones básicas
|--------------------------------------------------------------------------
*/

if (!$tipoTramiteId) {

    exit('
        El tipo de trámite es obligatorio.
        <br><br>
        <a href="crear.php">Regresar</a>
    ');
}


if (!$estadoId) {

    exit('
        El estado es obligatorio.
        <br><br>
        <a href="crear.php">Regresar</a>
    ');
}


if (!$etapaActualId) {

    exit('
        La etapa inicial es obligatoria.
        <br><br>
        <a href="crear.php">Regresar</a>
    ');
}


if (!$clienteId) {

    exit('
        Debes seleccionar un cliente.
        <br><br>
        <a href="crear.php">Regresar</a>
    ');
}


/*
|--------------------------------------------------------------------------
| Usuario creador
|--------------------------------------------------------------------------
*/

try {

    $creadoPor = obtenerUsuarioActual();

} catch (Throwable $e) {

    http_response_code(403);

    exit(
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/*
|--------------------------------------------------------------------------
| Iniciar transacción
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Validar cliente
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM personas
        WHERE
            id = :id
            AND activo = 1
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $clienteId
    ]);

    if (!$stmt->fetchColumn()) {

        throw new RuntimeException(
            'El cliente seleccionado no existe o está inactivo.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validar tipo de trámite
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM tipos_tramite
        WHERE
            id = :id
            AND activo = 1
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $tipoTramiteId
    ]);

    if (!$stmt->fetchColumn()) {

        throw new RuntimeException(
            'El tipo de trámite seleccionado no existe o está inactivo.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validar estado
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM estados_expediente
        WHERE
            id = :id
            AND activo = 1
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $estadoId
    ]);

    if (!$stmt->fetchColumn()) {

        throw new RuntimeException(
            'El estado seleccionado no existe o está inactivo.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validar etapa
    |--------------------------------------------------------------------------
    |
    | La etapa debe:
    |
    | 1. Existir.
    | 2. Estar activa.
    | 3. Estar configurada para el tipo de trámite.
    |
    */

    $stmt = $pdo->prepare("
        SELECT
            e.id

        FROM tipo_tramite_etapas tte

        INNER JOIN etapas e
            ON e.id = tte.etapa_id

        WHERE
            tte.tipo_tramite_id = :tipo_tramite_id
            AND tte.etapa_id = :etapa_id
            AND tte.activo = 1
            AND e.activo = 1

        LIMIT 1
    ");

    $stmt->execute([
        ':tipo_tramite_id' => $tipoTramiteId,
        ':etapa_id' => $etapaActualId
    ]);

    if (!$stmt->fetchColumn()) {

        throw new RuntimeException(
            'La etapa seleccionada no corresponde al tipo de trámite.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validar gestor
    |--------------------------------------------------------------------------
    */

    if ($gestorId) {

        $stmt = $pdo->prepare("
            SELECT
                u.id

            FROM usuarios u

            INNER JOIN roles r
                ON r.id = u.rol_id

            WHERE
                u.id = :usuario_id
                AND u.activo = 1
                AND r.activo = 1
                AND r.nombre = 'Gestor'

            LIMIT 1
        ");

        $stmt->execute([
            ':usuario_id' => $gestorId
        ]);

        if (!$stmt->fetchColumn()) {

            throw new RuntimeException(
                'El gestor seleccionado no existe o está inactivo.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Folio temporal
    |--------------------------------------------------------------------------
    |
    | Primero insertamos el expediente para obtener su ID.
    | Después generamos el folio definitivo.
    |
    */

    $folioTemporal =
        'TMP-' .
        date('YmdHis') .
        '-' .
        bin2hex(random_bytes(5));


    /*
    |--------------------------------------------------------------------------
    | Crear expediente
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO expedientes (
            numero_expediente,
            cliente_id,
            gestor_id,
            creado_por,
            tipo_tramite_id,
            estado_id,
            etapa_actual_id,
            observaciones,
            activo,
            eliminado
        )
        VALUES (
            :numero_expediente,
            :cliente_id,
            :gestor_id,
            :creado_por,
            :tipo_tramite_id,
            :estado_id,
            :etapa_actual_id,
            :observaciones,
            1,
            0
        )
    ");


    $stmt->execute([
        ':numero_expediente' => $folioTemporal,
        ':cliente_id' => $clienteId,
        ':gestor_id' => $gestorId ?: null,
        ':creado_por' => $creadoPor,
        ':tipo_tramite_id' => $tipoTramiteId,
        ':estado_id' => $estadoId,
        ':etapa_actual_id' => $etapaActualId,
        ':observaciones' =>
            $observaciones !== ''
                ? $observaciones
                : null
    ]);


    /*
    |--------------------------------------------------------------------------
    | ID generado
    |--------------------------------------------------------------------------
    */

    $expedienteId =
        (int) $pdo->lastInsertId();


    if (!$expedienteId) {

        throw new RuntimeException(
            'No se pudo obtener el ID del expediente creado.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generar número definitivo
    |--------------------------------------------------------------------------
    |
    | Ejemplo:
    |
    | EXP-2026-00001
    |
    | Utilizamos el ID del expediente para garantizar
    | que cada folio sea único.
    |
    */

    $anio =
        date('Y');

    $numeroExpediente =
        'EXP-' .
        $anio .
        '-' .
        str_pad(
            (string) $expedienteId,
            5,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Actualizar folio
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE expedientes

        SET
            numero_expediente = :numero_expediente

        WHERE
            id = :id
    ");

    $stmt->execute([
        ':numero_expediente' => $numeroExpediente,
        ':id' => $expedienteId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Confirmar
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Redirección
    |--------------------------------------------------------------------------
    */

    header(
        'Location: index.php?creado=1'
    );

    exit;


} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | Deshacer transacción
    |--------------------------------------------------------------------------
    */

    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();

    }


    http_response_code(500);

    ?>

    <!DOCTYPE html>

    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            Error al crear expediente
        </title>

        <style>

            body {

                margin: 0;

                min-height: 100vh;

                display: flex;

                align-items: center;

                justify-content: center;

                padding: 20px;

                box-sizing: border-box;

                font-family:
                    Arial,
                    Helvetica,
                    sans-serif;

                background: #f3f4f6;

            }


            .error {

                width: 100%;

                max-width: 500px;

                padding: 30px;

                box-sizing: border-box;

                background: #ffffff;

                border-radius: 16px;

                box-shadow:
                    0 10px 30px
                    rgba(0,0,0,.10);

            }


            h1 {

                margin-top: 0;

                color: #991b1b;

                font-size: 22px;

            }


            .message {

                padding: 14px;

                border-radius: 10px;

                background: #fef2f2;

                color: #7f1d1d;

                font-size: 13px;

                line-height: 1.5;

            }


            a {

                display: inline-block;

                margin-top: 18px;

                padding: 11px 16px;

                border-radius: 9px;

                background: #111827;

                color: #ffffff;

                text-decoration: none;

                font-size: 13px;

                font-weight: 700;

            }

        </style>

    </head>


    <body>

        <div class="error">

            <h1>
                No se pudo crear el expediente
            </h1>

            <div class="message">

                <?= htmlspecialchars(
                    $e->getMessage(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

            <a href="crear.php">
                ← Regresar al formulario
            </a>

        </div>

    </body>

    </html>

    <?php
}
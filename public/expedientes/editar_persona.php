<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/Helpers/permisos.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

try {

    /*
    |--------------------------------------------------------------------------
    | Permiso
    |--------------------------------------------------------------------------
    */

    exigirPermiso(
        $pdo,
        'expedientes',
        'crear'
    );


    /*
    |--------------------------------------------------------------------------
    | Recibir ID
    |--------------------------------------------------------------------------
    */

    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );


    if (!$id) {

        throw new RuntimeException(
            'ID de persona inválido.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Recibir datos
    |--------------------------------------------------------------------------
    */

    $nombre =
        trim(
            $_POST['nombre'] ?? ''
        );


    $apellidoPaterno =
        trim(
            $_POST['apellido_paterno'] ?? ''
        );


    $apellidoMaterno =
        trim(
            $_POST['apellido_materno'] ?? ''
        );


    $fechaNacimiento =
        trim(
            $_POST['fecha_nacimiento'] ?? ''
        );


    $estadoCivilId =
        filter_input(
            INPUT_POST,
            'estado_civil_id',
            FILTER_VALIDATE_INT
        );


    $curp =
        strtoupper(
            trim(
                $_POST['curp'] ?? ''
            )
        );


    $rfc =
        strtoupper(
            trim(
                $_POST['rfc'] ?? ''
            )
        );


    $telefono =
        trim(
            $_POST['telefono'] ?? ''
        );


    $correo =
        trim(
            $_POST['correo'] ?? ''
        );


    $domicilio =
        trim(
            $_POST['domicilio'] ?? ''
        );


    $observaciones =
        trim(
            $_POST['observaciones'] ?? ''
        );


    /*
    |--------------------------------------------------------------------------
    | Validaciones básicas
    |--------------------------------------------------------------------------
    */

    if ($nombre === '') {

        throw new RuntimeException(
            'El nombre es obligatorio.'
        );

    }


    if ($apellidoPaterno === '') {

        throw new RuntimeException(
            'El apellido paterno es obligatorio.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Validar fecha
    |--------------------------------------------------------------------------
    */

    if ($fechaNacimiento !== '') {

        $fecha =
            DateTime::createFromFormat(
                'Y-m-d',
                $fechaNacimiento
            );


        if (
            !$fecha
            ||
            $fecha->format('Y-m-d')
                !==
            $fechaNacimiento
        ) {

            throw new RuntimeException(
                'La fecha de nacimiento no es válida.'
            );

        }


        /*
        | No permitimos fechas futuras.
        */

        $hoy =
            new DateTime(
                'today'
            );


        if (
            $fecha > $hoy
        ) {

            throw new RuntimeException(
                'La fecha de nacimiento no puede ser futura.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validar estado civil
    |--------------------------------------------------------------------------
    */

    if ($estadoCivilId) {

        $stmt =
            $pdo->prepare("

                SELECT id

                FROM estados_civiles

                WHERE
                    id = :id
                    AND activo = 1

                LIMIT 1

            ");


        $stmt->execute([
            ':id' =>
                $estadoCivilId
        ]);


        if (!$stmt->fetchColumn()) {

            throw new RuntimeException(
                'El estado civil seleccionado no existe.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validar CURP
    |--------------------------------------------------------------------------
    */

    if ($curp !== '') {

        if (
            !preg_match(
                '/^[A-Z0-9]{18}$/',
                $curp
            )
        ) {

            throw new RuntimeException(
                'La CURP debe contener 18 caracteres.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validar RFC
    |--------------------------------------------------------------------------
    */

    if ($rfc !== '') {

        if (
            !preg_match(
                '/^[A-Z0-9]{12,13}$/',
                $rfc
            )
        ) {

            throw new RuntimeException(
                'El RFC debe contener entre 12 y 13 caracteres.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validar correo
    |--------------------------------------------------------------------------
    */

    if ($correo !== '') {

        if (
            !filter_var(
                $correo,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            throw new RuntimeException(
                'El correo electrónico no es válido.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Comprobar que exista la persona
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Obtener datos actuales antes de modificar
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("

            SELECT
                id,
                nombre,
                apellido_paterno,
                apellido_materno,
                fecha_nacimiento,
                estado_civil_id,
                curp,
                rfc,
                telefono,
                correo,
                domicilio,
                observaciones

            FROM personas

            WHERE
                id = :id
                AND activo = 1

            LIMIT 1

        ");

    $stmt->execute([
        ':id' => $id
    ]);

    $personaAnterior =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$personaAnterior) {

        throw new RuntimeException(
            'La persona no existe o está inactiva.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Evitar CURP duplicada
    |--------------------------------------------------------------------------
    */

    if ($curp !== '') {

        $stmt =
            $pdo->prepare("

                SELECT id

                FROM personas

                WHERE
                    curp = :curp
                    AND id <> :id
                    AND activo = 1

                LIMIT 1

            ");


        $stmt->execute([

            ':curp' =>
                $curp,

            ':id' =>
                $id

        ]);


        if ($stmt->fetchColumn()) {

            throw new RuntimeException(
                'La CURP ya está registrada en otra persona.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Evitar RFC duplicado
    |--------------------------------------------------------------------------
    */

    if ($rfc !== '') {

        $stmt =
            $pdo->prepare("

                SELECT id

                FROM personas

                WHERE
                    rfc = :rfc
                    AND id <> :id
                    AND activo = 1

                LIMIT 1

            ");


        $stmt->execute([

            ':rfc' =>
                $rfc,

            ':id' =>
                $id

        ]);


        if ($stmt->fetchColumn()) {

            throw new RuntimeException(
                'El RFC ya está registrado en otra persona.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Transacción
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Actualizar
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("

            UPDATE personas

            SET

                nombre = :nombre,

                apellido_paterno = :apellido_paterno,

                apellido_materno = :apellido_materno,

                fecha_nacimiento = :fecha_nacimiento,

                estado_civil_id = :estado_civil_id,

                curp = :curp,

                rfc = :rfc,

                telefono = :telefono,

                correo = :correo,

                domicilio = :domicilio,

                observaciones = :observaciones

            WHERE

                id = :id

                AND activo = 1

            LIMIT 1

        ");


    $stmt->execute([

        ':nombre' =>
            $nombre,

        ':apellido_paterno' =>
            $apellidoPaterno,

        ':apellido_materno' =>
            $apellidoMaterno !== ''
                ? $apellidoMaterno
                : null,

        ':fecha_nacimiento' =>
            $fechaNacimiento !== ''
                ? $fechaNacimiento
                : null,

        ':estado_civil_id' =>
            $estadoCivilId ?: null,

        ':curp' =>
            $curp !== ''
                ? $curp
                : null,

        ':rfc' =>
            $rfc !== ''
                ? $rfc
                : null,

        ':telefono' =>
            $telefono !== ''
                ? $telefono
                : null,

        ':correo' =>
            $correo !== ''
                ? $correo
                : null,

        ':domicilio' =>
            $domicilio !== ''
                ? $domicilio
                : null,

        ':observaciones' =>
            $observaciones !== ''
                ? $observaciones
                : null,

        ':id' =>
            $id

    ]);


    /*
    |--------------------------------------------------------------------------
    | Registrar historial de cambios
    |--------------------------------------------------------------------------
    */

    $camposHistorial = [

        'nombre' => 'Nombre',
        'apellido_paterno' => 'Apellido paterno',
        'apellido_materno' => 'Apellido materno',
        'fecha_nacimiento' => 'Fecha de nacimiento',
        'estado_civil_id' => 'Estado civil',
        'curp' => 'CURP',
        'rfc' => 'RFC',
        'telefono' => 'Teléfono',
        'correo' => 'Correo electrónico',
        'domicilio' => 'Domicilio',
        'observaciones' => 'Observaciones'

    ];

    $valoresNuevos = [

        'nombre' => $nombre,
        'apellido_paterno' => $apellidoPaterno,
        'apellido_materno' =>
            $apellidoMaterno !== '' ? $apellidoMaterno : null,
        'fecha_nacimiento' =>
            $fechaNacimiento !== '' ? $fechaNacimiento : null,
        'estado_civil_id' =>
            $estadoCivilId ?: null,
        'curp' =>
            $curp !== '' ? $curp : null,
        'rfc' =>
            $rfc !== '' ? $rfc : null,
        'telefono' =>
            $telefono !== '' ? $telefono : null,
        'correo' =>
            $correo !== '' ? $correo : null,
        'domicilio' =>
            $domicilio !== '' ? $domicilio : null,
        'observaciones' =>
            $observaciones !== '' ? $observaciones : null

    ];

    $cambios = [];

    foreach ($camposHistorial as $campo => $etiqueta) {

        $anterior = $personaAnterior[$campo] ?? null;
        $nuevo = $valoresNuevos[$campo] ?? null;

        $anteriorComparacion =
            $anterior === null ? null : (string) $anterior;

        $nuevoComparacion =
            $nuevo === null ? null : (string) $nuevo;

        if ($anteriorComparacion !== $nuevoComparacion) {

            $cambios[] = [
                'campo' => $campo,
                'etiqueta' => $etiqueta,
                'anterior' => $anterior,
                'nuevo' => $nuevo
            ];

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Expediente relacionado
    |--------------------------------------------------------------------------
    |
    | El modal de persona trabaja con persona_id y no necesita
    | expediente_id. Si existe una participación activa, tomamos
    | el expediente más reciente para relacionar el historial.
    |
    */

    $expedienteIdHistorial = null;

    $stmtExpediente =
        $pdo->prepare("

            SELECT expediente_id

            FROM expediente_personas

            WHERE
                persona_id = :persona_id
                AND activo = 1

            ORDER BY id DESC

            LIMIT 1

        ");

    $stmtExpediente->execute([
        ':persona_id' => $id
    ]);

    $expedienteIdHistorial =
        $stmtExpediente->fetchColumn();

    if ($expedienteIdHistorial !== false) {
        $expedienteIdHistorial =
            (int) $expedienteIdHistorial;
    } else {
        $expedienteIdHistorial = null;
    }


    /*
    |--------------------------------------------------------------------------
    | Usuario que realizó el cambio
    |--------------------------------------------------------------------------
    */

    $usuarioIdHistorial = null;

    foreach ([
        'usuario_id',
        'user_id',
        'id_usuario',
        'id'
    ] as $claveSesion) {

        if (
            isset($_SESSION[$claveSesion])
            && is_numeric($_SESSION[$claveSesion])
            && (int) $_SESSION[$claveSesion] > 0
        ) {

            $usuarioIdHistorial =
                (int) $_SESSION[$claveSesion];

            break;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Datos técnicos
    |--------------------------------------------------------------------------
    */

    $ip =
        $_SERVER['REMOTE_ADDR'] ?? null;

    $userAgent =
        $_SERVER['HTTP_USER_AGENT'] ?? null;


    if ($cambios) {

        $stmtHistorial =
            $pdo->prepare("

                INSERT INTO historial (

                    usuario_id,
                    expediente_id,
                    entidad,
                    entidad_id,
                    accion,
                    descripcion,
                    campo,
                    valor_anterior,
                    valor_nuevo,
                    ip,
                    user_agent

                ) VALUES (

                    :usuario_id,
                    :expediente_id,
                    'personas',
                    :entidad_id,
                    'ACTUALIZAR',
                    :descripcion,
                    :campo,
                    :valor_anterior,
                    :valor_nuevo,
                    :ip,
                    :user_agent

                )

            ");

        foreach ($cambios as $cambio) {

            $descripcion =
                'Actualización de datos personales: '
                . $cambio['etiqueta'];

            $stmtHistorial->execute([

                ':usuario_id' =>
                    $usuarioIdHistorial,

                ':expediente_id' =>
                    $expedienteIdHistorial,

                ':entidad_id' =>
                    $id,

                ':descripcion' =>
                    $descripcion,

                ':campo' =>
                    $cambio['campo'],

                ':valor_anterior' =>
                    $cambio['anterior'] === null
                        ? null
                        : (string) $cambio['anterior'],

                ':valor_nuevo' =>
                    $cambio['nuevo'] === null
                        ? null
                        : (string) $cambio['nuevo'],

                ':ip' =>
                    $ip,

                ':user_agent' =>
                    $userAgent

            ]);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Confirmar transacción
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Obtener persona actualizada
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("

            SELECT

                p.id,

                p.nombre,

                p.apellido_paterno,

                p.apellido_materno,

                p.fecha_nacimiento,

                p.estado_civil_id,

                ec.nombre AS estado_civil,

                p.curp,

                p.rfc,

                p.telefono,

                p.correo,

                p.domicilio,

                p.observaciones

            FROM personas p

            LEFT JOIN estados_civiles ec
                ON ec.id = p.estado_civil_id

            WHERE
                p.id = :id

                AND p.activo = 1

            LIMIT 1

        ");


    $stmt->execute([
        ':id' =>
            $id
    ]);


    $persona =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$persona) {

        throw new RuntimeException(
            'No fue posible recuperar los datos actualizados.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Respuesta
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        'success' => true,

        'message' =>
            'Datos de la persona actualizados correctamente.',

        'persona' =>
            $persona

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    if (
        isset($pdo)
        && $pdo instanceof PDO
        && $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
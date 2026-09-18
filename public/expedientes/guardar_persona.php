<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/Helpers/permisos.php';

header(
    'Content-Type: application/json; charset=utf-8'
);

try {

    exigirPermiso(
        $pdo,
        'expedientes',
        'crear'
    );


    /*
    |--------------------------------------------------------------------------
    | Datos
    |--------------------------------------------------------------------------
    */

    $nombre =
        trim($_POST['nombre'] ?? '');

    $apellidoPaterno =
        trim($_POST['apellido_paterno'] ?? '');

    $apellidoMaterno =
        trim($_POST['apellido_materno'] ?? '');

    $fechaNacimiento =
        trim($_POST['fecha_nacimiento'] ?? '');

    $estadoCivilId =
        filter_input(
            INPUT_POST,
            'estado_civil_id',
            FILTER_VALIDATE_INT
        );

    $curp =
        strtoupper(
            trim($_POST['curp'] ?? '')
        );

    $rfc =
        strtoupper(
            trim($_POST['rfc'] ?? '')
        );

    $telefono =
        trim($_POST['telefono'] ?? '');

    $correo =
        trim($_POST['correo'] ?? '');

    $domicilio =
        trim($_POST['domicilio'] ?? '');

    $observaciones =
        trim($_POST['observaciones'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validaciones
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


    if (
        $correo !== ''
        && !filter_var(
            $correo,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        throw new RuntimeException(
            'El correo electrónico no es válido.'
        );
    }


    if (
        $curp !== ''
        && !preg_match(
            '/^[A-Z0-9]{18}$/',
            $curp
        )
    ) {

        throw new RuntimeException(
            'La CURP debe contener 18 caracteres.'
        );
    }


    if (
        $rfc !== ''
        && !preg_match(
            '/^[A-Z0-9]{12,13}$/',
            $rfc
        )
    ) {

        throw new RuntimeException(
            'El RFC debe contener entre 12 y 13 caracteres.'
        );
    }


    if ($fechaNacimiento !== '') {

        $fecha =
            DateTime::createFromFormat(
                'Y-m-d',
                $fechaNacimiento
            );

        if (
            !$fecha
            || $fecha->format('Y-m-d')
                !== $fechaNacimiento
        ) {

            throw new RuntimeException(
                'La fecha de nacimiento no es válida.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Iniciar transacción
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


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
                WHERE id = :id
                LIMIT 1
            ");

        $stmt->execute([
            ':id' => $estadoCivilId
        ]);

        if (!$stmt->fetchColumn()) {

            throw new RuntimeException(
                'El estado civil seleccionado no existe.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Evitar duplicados por CURP
    |--------------------------------------------------------------------------
    */

    if ($curp !== '') {

        $stmt =
            $pdo->prepare("
                SELECT id
                FROM personas
                WHERE curp = :curp
                LIMIT 1
            ");

        $stmt->execute([
            ':curp' => $curp
        ]);

        if ($stmt->fetchColumn()) {

            throw new RuntimeException(
                'Ya existe una persona registrada con esa CURP.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Crear PERSONA
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("
            INSERT INTO personas (
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
                observaciones,
                activo
            )
            VALUES (
                :nombre,
                :apellido_paterno,
                :apellido_materno,
                :fecha_nacimiento,
                :estado_civil_id,
                :curp,
                :rfc,
                :telefono,
                :correo,
                :domicilio,
                :observaciones,
                1
            )
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
                : null

    ]);


    $personaId =
        (int) $pdo->lastInsertId();


    if (!$personaId) {

        throw new RuntimeException(
            'No se pudo crear la persona.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Crear CLIENTE
    |--------------------------------------------------------------------------
    |
    | Aquí está la corrección importante.
    |
    | persona.id
    |      ↓
    | clientes.persona_id
    |      ↓
    | clientes.id
    |      ↓
    | expedientes.cliente_id
    |
    */

    $stmt =
        $pdo->prepare("
            INSERT INTO clientes (
                persona_id,
                activo
            )
            VALUES (
                :persona_id,
                1
            )
        ");


    $stmt->execute([
        ':persona_id' =>
            $personaId
    ]);


    $clienteId =
        (int) $pdo->lastInsertId();


    if (!$clienteId) {

        throw new RuntimeException(
            'No se pudo crear el registro de cliente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Confirmar
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Nombre completo
    |--------------------------------------------------------------------------
    */

    $nombreCompleto =
        trim(
            $nombre
            . ' '
            . $apellidoPaterno
            . ' '
            . $apellidoMaterno
        );


    /*
    |--------------------------------------------------------------------------
    | Respuesta
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        'success' => true,

        'message' =>
            'Persona y cliente creados correctamente.',

        'persona' => [

            'id' =>
                $personaId,

            'cliente_id' =>
                $clienteId,

            'nombre_completo' =>
                $nombreCompleto,

            'curp' =>
                $curp,

            'rfc' =>
                $rfc

        ]

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {


    if (
        $pdo->inTransaction()
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
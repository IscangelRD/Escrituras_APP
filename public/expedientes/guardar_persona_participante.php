<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/Helpers/permisos.php';

header('Content-Type: application/json; charset=utf-8');

try {

    exigirPermiso(
        $pdo,
        'expedientes',
        'crear'
    );

    $nombre = trim($_POST['nombre'] ?? '');

    $apellidoPaterno =
        trim($_POST['apellido_paterno'] ?? '');

    $apellidoMaterno =
        trim($_POST['apellido_materno'] ?? '');

    $fechaNacimiento =
        trim($_POST['fecha_nacimiento'] ?? '');

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
    | Verificar CURP
    |--------------------------------------------------------------------------
    */

    if ($curp !== '') {

        $stmt = $pdo->prepare("

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
                'Ya existe una persona con esa CURP.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Verificar RFC
    |--------------------------------------------------------------------------
    */

    if ($rfc !== '') {

        $stmt = $pdo->prepare("

            SELECT id

            FROM personas

            WHERE rfc = :rfc

            LIMIT 1

        ");

        $stmt->execute([
            ':rfc' => $rfc
        ]);

        if ($stmt->fetchColumn()) {

            throw new RuntimeException(
                'Ya existe una persona con ese RFC.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Insertar persona
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        INSERT INTO personas (

            nombre,
            apellido_paterno,
            apellido_materno,
            fecha_nacimiento,
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


    $nombreCompleto =
        trim(
            $nombre
            . ' '
            . $apellidoPaterno
            . ' '
            . $apellidoMaterno
        );


    echo json_encode([

        'success' => true,

        'message' =>
            'Persona creada correctamente.',

        'persona' => [

            'id' =>
                $personaId,

            'nombre_completo' =>
                $nombreCompleto

        ]

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
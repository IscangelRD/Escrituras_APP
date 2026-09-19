<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/Helpers/permisos.php';

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
    | ID de persona
    |--------------------------------------------------------------------------
    */

    $id = filter_input(
        INPUT_GET,
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
    | Obtener persona
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

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
        ':id' => $id
    ]);


    $persona =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$persona) {

        throw new RuntimeException(
            'La persona no existe o está inactiva.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Edad calculada
    |--------------------------------------------------------------------------
    */

    $edad = null;


    if (
        !empty(
            $persona['fecha_nacimiento']
        )
    ) {

        $fechaNacimiento =
            new DateTime(
                $persona['fecha_nacimiento']
            );


        $hoy =
            new DateTime(
                'today'
            );


        $edad =
            $fechaNacimiento
                ->diff($hoy)
                ->y;

    }


    $persona['edad'] =
        $edad;


    /*
    |--------------------------------------------------------------------------
    | Respuesta
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        'success' => true,

        'persona' => $persona

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
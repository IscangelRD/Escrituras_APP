<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/Helpers/permisos.php';


/*
|--------------------------------------------------------------------------
| RESPUESTA JSON
|--------------------------------------------------------------------------
*/

header(
    'Content-Type: application/json; charset=utf-8'
);


try {

    /*
    |--------------------------------------------------------------------------
    | PERMISO
    |--------------------------------------------------------------------------
    */

    exigirPermiso(
        $pdo,
        'expedientes',
        'ver'
    );


    /*
    |--------------------------------------------------------------------------
    | ID DEL EXPEDIENTE
    |--------------------------------------------------------------------------
    */

    $expedienteId =
        filter_input(
            INPUT_GET,
            'expediente_id',
            FILTER_VALIDATE_INT
        );


    if (!$expedienteId) {

        throw new RuntimeException(
            'ID de expediente inválido.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR QUE EXISTE EL EXPEDIENTE
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("

            SELECT id

            FROM expedientes

            WHERE
                id = ?
                AND activo = 1
                AND eliminado = 0

            LIMIT 1

        ");


    $stmt->execute([
        $expedienteId
    ]);


    if (!$stmt->fetchColumn()) {

        throw new RuntimeException(
            'El expediente no existe o está inactivo.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | HISTORIAL
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("

            SELECT

                h.id,

                h.usuario_id,

                h.expediente_id,

                h.entidad,

                h.entidad_id,

                h.accion,

                h.descripcion,

                h.campo,

                h.valor_anterior,

                h.valor_nuevo,

                h.ip,

                h.user_agent,

                h.fecha_hora,


                CONCAT(

                    COALESCE(
                        u.nombre,
                        ''
                    ),

                    ' ',

                    COALESCE(
                        u.apellido_paterno,
                        ''
                    ),

                    ' ',

                    COALESCE(
                        u.apellido_materno,
                        ''
                    )

                ) AS usuario_nombre


            FROM historial h


            LEFT JOIN usuarios u

                ON u.id =
                   h.usuario_id


            WHERE

                h.expediente_id = ?


            ORDER BY

                h.fecha_hora DESC,

                h.id DESC


            LIMIT 100

        ");


    $stmt->execute([
        $expedienteId
    ]);


    $historial =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


    /*
    |--------------------------------------------------------------------------
    | RESPUESTA
    |--------------------------------------------------------------------------
    */

    echo json_encode(

        [

            'success' =>
                true,

            'historial' =>
                $historial

        ],

        JSON_UNESCAPED_UNICODE
        |
        JSON_UNESCAPED_SLASHES

    );


} catch (Throwable $e) {

    http_response_code(400);


    echo json_encode(

        [

            'success' =>
                false,

            'message' =>
                $e->getMessage()

        ],

        JSON_UNESCAPED_UNICODE

    );

}
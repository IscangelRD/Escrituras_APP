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


    $busqueda =
        trim(
            $_GET['q'] ?? ''
        );


    if (
        mb_strlen($busqueda) < 2
    ) {

        throw new RuntimeException(
            'La búsqueda debe tener al menos 2 caracteres.'
        );
    }


    $texto =
        '%' . $busqueda . '%';


    /*
    |--------------------------------------------------------------------------
    | Buscar CLIENTES
    |--------------------------------------------------------------------------
    |
    | clientes.id será el valor que irá a:
    |
    | expedientes.cliente_id
    |
    */

    $sql = "

        SELECT

            c.id AS cliente_id,

            p.id AS persona_id,

            p.nombre,

            p.apellido_paterno,

            p.apellido_materno,

            p.curp,

            p.rfc,

            CONCAT(
                p.nombre,
                ' ',
                p.apellido_paterno,
                ' ',
                COALESCE(
                    p.apellido_materno,
                    ''
                )
            ) AS nombre_completo

        FROM clientes c

        INNER JOIN personas p
            ON p.id = c.persona_id

        WHERE

            c.activo = 1

            AND p.activo = 1

            AND (

                p.nombre LIKE :busqueda1

                OR p.apellido_paterno LIKE :busqueda2

                OR p.apellido_materno LIKE :busqueda3

                OR p.curp LIKE :busqueda4

                OR p.rfc LIKE :busqueda5

                OR CONCAT(
                    p.nombre,
                    ' ',
                    p.apellido_paterno,
                    ' ',
                    COALESCE(
                        p.apellido_materno,
                        ''
                    )
                ) LIKE :busqueda6

            )

        ORDER BY

            p.nombre ASC,

            p.apellido_paterno ASC

        LIMIT 20

    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute([

        ':busqueda1' =>
            $texto,

        ':busqueda2' =>
            $texto,

        ':busqueda3' =>
            $texto,

        ':busqueda4' =>
            $texto,

        ':busqueda5' =>
            $texto,

        ':busqueda6' =>
            $texto

    ]);


    $personas =
        $stmt->fetchAll();


    echo json_encode([

        'success' =>
            true,

        'personas' =>
            $personas

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([

        'success' =>
            false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
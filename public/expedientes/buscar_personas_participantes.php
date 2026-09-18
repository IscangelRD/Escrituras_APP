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
        'ver'
    );


    $q = trim(
        $_GET['q'] ?? ''
    );


    if (
        mb_strlen($q) < 2
    ) {

        throw new RuntimeException(
            'Escribe al menos 2 caracteres.'
        );

    }


    $buscar =
        '%' . $q . '%';


    $stmt = $pdo->prepare("

        SELECT

            id,

            nombre,

            apellido_paterno,

            apellido_materno,

            curp,

            rfc

        FROM personas

        WHERE

            activo = 1

            AND (

                nombre LIKE :q1

                OR apellido_paterno LIKE :q2

                OR apellido_materno LIKE :q3

                OR curp LIKE :q4

                OR rfc LIKE :q5

                OR CONCAT(
                    nombre,
                    ' ',
                    apellido_paterno,
                    ' ',
                    COALESCE(
                        apellido_materno,
                        ''
                    )
                ) LIKE :q6

            )

        ORDER BY
            nombre ASC,
            apellido_paterno ASC

        LIMIT 20

    ");


    $stmt->execute([

        ':q1' => $buscar,
        ':q2' => $buscar,
        ':q3' => $buscar,
        ':q4' => $buscar,
        ':q5' => $buscar,
        ':q6' => $buscar

    ]);


    $personas =
        $stmt->fetchAll();


    foreach ($personas as &$persona) {

        $persona['nombre_completo'] =
            trim(
                $persona['nombre']
                . ' '
                . $persona['apellido_paterno']
                . ' '
                . ($persona['apellido_materno'] ?? '')
            );

    }

    unset($persona);


    echo json_encode([

        'success' => true,

        'personas' => $personas

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
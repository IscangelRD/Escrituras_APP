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


    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );


    if (!$id) {

        throw new RuntimeException(
            'Participante inválido.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Comprobar que exista y esté activo
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        SELECT
            id,
            expediente_id,
            persona_id

        FROM expediente_personas

        WHERE
            id = :id
            AND activo = 1

        LIMIT 1

    ");


    $stmt->execute([
        ':id' => $id
    ]);


    $participante =
        $stmt->fetch();


    if (!$participante) {

        throw new RuntimeException(
            'El participante no existe o ya fue retirado.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Desactivar
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        UPDATE expediente_personas

        SET activo = 0

        WHERE
            id = :id

        LIMIT 1

    ");


    $stmt->execute([
        ':id' => $id
    ]);


    echo json_encode([

        'success' => true,

        'message' =>
            'Participante retirado correctamente.'

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
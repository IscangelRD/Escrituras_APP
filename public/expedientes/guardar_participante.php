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


    $expedienteId = filter_input(
        INPUT_POST,
        'expediente_id',
        FILTER_VALIDATE_INT
    );

    $personaId = filter_input(
        INPUT_POST,
        'persona_id',
        FILTER_VALIDATE_INT
    );

    $rolId = filter_input(
        INPUT_POST,
        'rol_participante_id',
        FILTER_VALIDATE_INT
    );

    $personaRepresentadaId = filter_input(
        INPUT_POST,
        'persona_representada_id',
        FILTER_VALIDATE_INT
    );


    $observaciones =
        trim(
            $_POST['observaciones'] ?? ''
        );


    if (!$expedienteId) {

        throw new RuntimeException(
            'Expediente inválido.'
        );

    }


    if (!$personaId) {

        throw new RuntimeException(
            'Debes seleccionar una persona.'
        );

    }


    if (!$rolId) {

        throw new RuntimeException(
            'Debes seleccionar un rol.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Verificar expediente
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        SELECT id

        FROM expedientes

        WHERE id = :id

        LIMIT 1

    ");

    $stmt->execute([
        ':id' => $expedienteId
    ]);


    if (!$stmt->fetchColumn()) {

        throw new RuntimeException(
            'El expediente no existe.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Verificar persona
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
        ':id' => $personaId
    ]);


    if (!$stmt->fetchColumn()) {

        throw new RuntimeException(
            'La persona seleccionada no existe.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Verificar rol
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        SELECT id, nombre

        FROM roles_participante

        WHERE

            id = :id

            AND activo = 1

        LIMIT 1

    ");

    $stmt->execute([
        ':id' => $rolId
    ]);


    $rol =
        $stmt->fetch();


    if (!$rol) {

        throw new RuntimeException(
            'El rol seleccionado no existe.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Verificar representado
    |--------------------------------------------------------------------------
    */

    if ($personaRepresentadaId) {

        $stmt = $pdo->prepare("

            SELECT id

            FROM personas

            WHERE

                id = :id

                AND activo = 1

            LIMIT 1

        ");

        $stmt->execute([
            ':id' => $personaRepresentadaId
        ]);


        if (!$stmt->fetchColumn()) {

            throw new RuntimeException(
                'La persona representada no existe.'
            );

        }


        if (
            $personaRepresentadaId
            === $personaId
        ) {

            throw new RuntimeException(
                'Una persona no puede representarse a sí misma.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Evitar duplicado
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        SELECT id

        FROM expediente_personas

        WHERE

            expediente_id = :expediente_id

            AND persona_id = :persona_id

            AND rol_participante_id = :rol_id

            AND activo = 1

        LIMIT 1

    ");

    $stmt->execute([

        ':expediente_id' =>
            $expedienteId,

        ':persona_id' =>
            $personaId,

        ':rol_id' =>
            $rolId

    ]);


    if ($stmt->fetchColumn()) {

        throw new RuntimeException(
            'Esta persona ya tiene ese rol dentro del expediente.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        INSERT INTO expediente_personas (

            expediente_id,

            persona_id,

            rol_participante_id,

            persona_representada_id,

            observaciones,

            activo

        )

        VALUES (

            :expediente_id,

            :persona_id,

            :rol_id,

            :persona_representada_id,

            :observaciones,

            1

        )

    ");


    $stmt->execute([

        ':expediente_id' =>
            $expedienteId,

        ':persona_id' =>
            $personaId,

        ':rol_id' =>
            $rolId,

        ':persona_representada_id' =>
            $personaRepresentadaId ?: null,

        ':observaciones' =>
            $observaciones !== ''
                ? $observaciones
                : null

    ]);


    $participanteId =
        (int) $pdo->lastInsertId();


    echo json_encode([

        'success' => true,

        'message' =>
            'Participante agregado correctamente.',

        'id' =>
            $participanteId

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
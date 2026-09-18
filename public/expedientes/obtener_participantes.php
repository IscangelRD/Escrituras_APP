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

    $expedienteId = filter_input(
        INPUT_GET,
        'expediente_id',
        FILTER_VALIDATE_INT
    );

    if (!$expedienteId) {
        throw new RuntimeException(
            'Expediente inválido.'
        );
    }


    $stmt = $pdo->prepare("

        SELECT

            ep.id,

            ep.expediente_id,

            ep.persona_id,

            ep.rol_participante_id,

            ep.persona_representada_id,

            ep.observaciones,

            p.nombre,

            p.apellido_paterno,

            p.apellido_materno,

            p.curp,

            p.rfc,

            r.nombre AS rol_nombre,

            rp.nombre AS representada_nombre,

            rp.apellido_paterno AS representada_apellido_paterno,

            rp.apellido_materno AS representada_apellido_materno

        FROM expediente_personas ep

        INNER JOIN personas p
            ON p.id = ep.persona_id

        INNER JOIN roles_participante r
            ON r.id = ep.rol_participante_id

        LEFT JOIN personas rp
            ON rp.id = ep.persona_representada_id

        WHERE

            ep.expediente_id = :expediente_id

            AND ep.activo = 1

        ORDER BY
            ep.id ASC

    ");

    $stmt->execute([
        ':expediente_id' => $expedienteId
    ]);

    $participantes = $stmt->fetchAll();


    foreach ($participantes as &$participante) {

        $participante['nombre_completo'] =
            trim(
                $participante['nombre']
                . ' '
                . $participante['apellido_paterno']
                . ' '
                . ($participante['apellido_materno'] ?? '')
            );


        if (
            !empty(
                $participante['representada_nombre']
            )
        ) {

            $participante['representada_nombre_completo'] =
                trim(
                    $participante['representada_nombre']
                    . ' '
                    . $participante['representada_apellido_paterno']
                    . ' '
                    . ($participante['representada_apellido_materno'] ?? '')
                );

        } else {

            $participante['representada_nombre_completo'] = null;

        }

    }

    unset($participante);


    echo json_encode([
        'success' => true,
        'participantes' => $participantes
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}
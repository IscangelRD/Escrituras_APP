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


    $tipoTramiteId =
        filter_input(
            INPUT_GET,
            'tipo_tramite_id',
            FILTER_VALIDATE_INT
        );


    if (!$tipoTramiteId) {

        throw new RuntimeException(
            'Tipo de trámite inválido.'
        );
    }


    $sql = "
        SELECT

            e.id,
            e.nombre,
            e.descripcion,
            e.orden,

            tte.obligatoria

        FROM tipo_tramite_etapas tte

        INNER JOIN etapas e
            ON e.id = tte.etapa_id

        WHERE
            tte.tipo_tramite_id = :tipo_tramite_id
            AND tte.activo = 1
            AND e.activo = 1

        ORDER BY
            tte.orden ASC,
            e.orden ASC,
            e.id ASC
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute([
        ':tipo_tramite_id' =>
            $tipoTramiteId
    ]);


    $etapas =
        $stmt->fetchAll();


    echo json_encode([
        'success' => true,
        'etapas' => $etapas
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}
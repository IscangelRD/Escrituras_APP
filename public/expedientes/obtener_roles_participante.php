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


    $stmt = $pdo->query("

        SELECT
            id,
            nombre,
            descripcion

        FROM roles_participante

        WHERE activo = 1

        ORDER BY id ASC

    ");


    echo json_encode([
        'success' => true,
        'roles' => $stmt->fetchAll()
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}
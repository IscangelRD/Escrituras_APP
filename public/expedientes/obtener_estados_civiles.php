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

    $stmt = $pdo->query("

        SELECT
            id,
            nombre

        FROM estados_civiles

        WHERE activo = 1

        ORDER BY id ASC

    ");

    $estados = $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );

    echo json_encode([
        'success' => true,
        'estados' => $estados
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
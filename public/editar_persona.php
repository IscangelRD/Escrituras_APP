<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/permisos.php';

header('Content-Type: application/json; charset=utf-8');

try {

    exigirPermiso(
        $pdo,
        'expedientes',
        'crear'
    );

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        throw new Exception(
            'Método no permitido.'
        );

    }

    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );

    if (!$id) {

        throw new Exception(
            'ID de persona no válido.'
        );

    }

    $nombre = trim(
        $_POST['nombre'] ?? ''
    );

    $apellidoPaterno = trim(
        $_POST['apellido_paterno'] ?? ''
    );

    $apellidoMaterno = trim(
        $_POST['apellido_materno'] ?? ''
    );

    $fechaNacimiento = trim(
        $_POST['fecha_nacimiento'] ?? ''
    );

    $estadoCivilId = trim(
        $_POST['estado_civil_id'] ?? ''
    );

    $curp = strtoupper(
        trim(
            $_POST['curp'] ?? ''
        )
    );

    $rfc = strtoupper(
        trim(
            $_POST['rfc'] ?? ''
        )
    );

    $telefono = trim(
        $_POST['telefono'] ?? ''
    );

    $correo = trim(
        $_POST['correo'] ?? ''
    );

    $domicilio = trim(
        $_POST['domicilio'] ?? ''
    );

    $observaciones = trim(
        $_POST['observaciones'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    if ($nombre === '') {

        throw new Exception(
            'El nombre es obligatorio.'
        );

    }

    if ($apellidoPaterno === '') {

        throw new Exception(
            'El apellido paterno es obligatorio.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar valores vacíos a NULL
    |--------------------------------------------------------------------------
    */

    $apellidoMaterno =
        $apellidoMaterno !== ''
            ? $apellidoMaterno
            : null;

    $fechaNacimiento =
        $fechaNacimiento !== ''
            ? $fechaNacimiento
            : null;

    $estadoCivilId =
        $estadoCivilId !== ''
            ? (int)$estadoCivilId
            : null;

    $curp =
        $curp !== ''
            ? $curp
            : null;

    $rfc =
        $rfc !== ''
            ? $rfc
            : null;

    $telefono =
        $telefono !== ''
            ? $telefono
            : null;

    $correo =
        $correo !== ''
            ? $correo
            : null;

    $domicilio =
        $domicilio !== ''
            ? $domicilio
            : null;

    $observaciones =
        $observaciones !== ''
            ? $observaciones
            : null;


    /*
    |--------------------------------------------------------------------------
    | Verificar que exista la persona
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM personas
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $id
    ]);

    if (!$stmt->fetch()) {

        throw new Exception(
            'La persona no existe.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar persona
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("

        UPDATE personas
        SET
            nombre = ?,
            apellido_paterno = ?,
            apellido_materno = ?,
            fecha_nacimiento = ?,
            estado_civil_id = ?,
            curp = ?,
            rfc = ?,
            telefono = ?,
            correo = ?,
            domicilio = ?,
            observaciones = ?,
            updated_at = CURRENT_TIMESTAMP

        WHERE id = ?

    ");

    $stmt->execute([

        $nombre,
        $apellidoPaterno,
        $apellidoMaterno,
        $fechaNacimiento,
        $estadoCivilId,
        $curp,
        $rfc,
        $telefono,
        $correo,
        $domicilio,
        $observaciones,
        $id

    ]);


    /*
    |--------------------------------------------------------------------------
    | Obtener datos actualizados
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
            p.curp,
            p.rfc,
            p.telefono,
            p.correo,
            p.domicilio,
            p.observaciones

        FROM personas p

        WHERE p.id = ?

        LIMIT 1

    ");

    $stmt->execute([
        $id
    ]);

    $persona =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    echo json_encode([

        'success' => true,

        'message' =>
            'Datos personales actualizados correctamente.',

        'persona' =>
            $persona

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
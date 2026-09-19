<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/Helpers/permisos.php';

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

$expedienteId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$expedienteId) {

    http_response_code(400);

    exit('
        ID de expediente inválido.
        <br><br>
        <a href="index.php">Regresar</a>
    ');
}


/*
|--------------------------------------------------------------------------
| CONSULTAR EXPEDIENTE
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        e.id,
        e.numero_expediente,

        e.cliente_id,
        e.gestor_id,
        e.creado_por,

        e.tipo_tramite_id,
        e.estado_id,
        e.etapa_actual_id,

        e.observaciones,

        e.fecha_creacion,
        e.fecha_actualizacion,
        e.fecha_finalizacion,
        e.fecha_cancelacion,
        e.fecha_archivado,

        e.activo,
        e.eliminado,

        /* CLIENTE */

        c.persona_id AS cliente_persona_id,

        p.nombre AS cliente_nombre,
        p.apellido_paterno AS cliente_apellido_paterno,
        p.apellido_materno AS cliente_apellido_materno,

        p.fecha_nacimiento AS cliente_fecha_nacimiento,
        p.curp AS cliente_curp,
        p.rfc AS cliente_rfc,
        p.telefono AS cliente_telefono,
        p.correo AS cliente_correo,
        p.domicilio AS cliente_domicilio,

        /* TRÁMITE */

        tt.nombre AS tipo_tramite_nombre,
        tt.descripcion AS tipo_tramite_descripcion,

        /* ESTADO */

        ee.nombre AS estado_nombre,
        ee.descripcion AS estado_descripcion,

        /* ETAPA */

        et.nombre AS etapa_nombre,
        et.descripcion AS etapa_descripcion,
        et.orden AS etapa_orden,

        /* GESTOR */

        CONCAT(
            COALESCE(g.nombre, ''),
            ' ',
            COALESCE(g.apellido_paterno, ''),
            ' ',
            COALESCE(g.apellido_materno, '')
        ) AS gestor_nombre,

        /* CREADOR */

        CONCAT(
            COALESCE(u.nombre, ''),
            ' ',
            COALESCE(u.apellido_paterno, ''),
            ' ',
            COALESCE(u.apellido_materno, '')
        ) AS creador_nombre

    FROM expedientes e

    /* CLIENTE */

    INNER JOIN clientes c
        ON c.id = e.cliente_id

    INNER JOIN personas p
        ON p.id = c.persona_id

    /* TIPO DE TRÁMITE */

    INNER JOIN tipos_tramite tt
        ON tt.id = e.tipo_tramite_id

    /* ESTADO */

    INNER JOIN estados_expediente ee
        ON ee.id = e.estado_id

    /* ETAPA */

    INNER JOIN etapas et
        ON et.id = e.etapa_actual_id

    /* GESTOR */

    LEFT JOIN usuarios g
        ON g.id = e.gestor_id

    /* CREADOR */

    INNER JOIN usuarios u
        ON u.id = e.creado_por

    WHERE
        e.id = :id

    LIMIT 1

";


$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $expedienteId
]);

$expediente = $stmt->fetch();


if (!$expediente) {

    http_response_code(404);

    exit('
        Expediente no encontrado.
        <br><br>
        <a href="index.php">Regresar</a>
    ');
}


/*
|--------------------------------------------------------------------------
| NOMBRE CLIENTE
|--------------------------------------------------------------------------
*/

$nombreCliente = trim(
    $expediente['cliente_nombre']
        . ' '
        . $expediente['cliente_apellido_paterno']
        . ' '
        . ($expediente['cliente_apellido_materno'] ?? '')
);


/*
|--------------------------------------------------------------------------
| NOMBRE GESTOR
|--------------------------------------------------------------------------
*/

$nombreGestor =
    trim(
        $expediente['gestor_nombre'] ?? ''
    );

if ($nombreGestor === '') {

    $nombreGestor =
        'Sin asignar';
}


/*
|--------------------------------------------------------------------------
| ESTADO VISUAL
|--------------------------------------------------------------------------
*/

$estadoNombre =
    strtoupper(
        trim(
            $expediente['estado_nombre']
        )
    );


$estadoClase = 'status-neutral';


if (
    str_contains(
        $estadoNombre,
        'CERR'
    )
) {

    $estadoClase =
        'status-success';
} elseif (
    str_contains(
        $estadoNombre,
        'CANCEL'
    )
) {

    $estadoClase =
        'status-danger';
} elseif (
    str_contains(
        $estadoNombre,
        'DETEN'
    )
) {

    $estadoClase =
        'status-warning';
} else {

    $estadoClase =
        'status-process';
}


/*
|--------------------------------------------------------------------------
| FECHAS
|--------------------------------------------------------------------------
*/

function fechaMostrar(
    ?string $fecha
): string {

    if (
        !$fecha
        || $fecha === '0000-00-00 00:00:00'
    ) {

        return '—';
    }


    try {

        $date =
            new DateTime(
                $fecha
            );

        return $date->format(
            'd/m/Y H:i'
        );
    } catch (Throwable $e) {

        return $fecha;
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="theme-color"
        content="#111827">

    <title>

        <?= htmlspecialchars(
            $expediente['numero_expediente'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </title>


    <link
        rel="stylesheet"
        href="../css/dashboard.css">

    <link
        rel="stylesheet"
        href="../css/expedientes.css">

    <link
        rel="stylesheet"
        href="../css/ver-expediente.css?v=1">

</head>


<body>

    <div class="app">


        <!-- ==================================================
         SIDEBAR
    =================================================== -->

        <aside
            class="sidebar"
            id="sidebar">

            <div class="sidebar-header">

                <div class="brand-logo">
                    IE
                </div>

                <div>

                    <strong>
                        Sistema de Escrituras
                    </strong>

                    <small>
                        Gestión de expedientes
                    </small>

                </div>

            </div>


            <nav class="menu">

                <a
                    href="../index.php"
                    class="menu-item">
                    <span>🏠</span>
                    <span>Dashboard</span>
                </a>


                <a
                    href="index.php"
                    class="menu-item active">
                    <span>📁</span>
                    <span>Expedientes</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>👥</span>
                    <span>Personas</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>🏠</span>
                    <span>Inmuebles</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>📎</span>
                    <span>Documentos</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>🔎</span>
                    <span>Revisiones</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>📝</span>
                    <span>Observaciones</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>💰</span>
                    <span>Finanzas</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>🏛️</span>
                    <span>Impuestos</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>🔔</span>
                    <span>Notificaciones</span>
                </a>


                <a
                    href="#"
                    class="menu-item">
                    <span>🏆</span>
                    <span>Logros</span>
                </a>

            </nav>


            <div class="sidebar-footer">

                <a
                    href="../logout.php"
                    class="logout-button">
                    🚪 Cerrar sesión
                </a>

            </div>

        </aside>


        <!-- ==================================================
         MAIN
    =================================================== -->

        <main class="main">


            <!-- TOPBAR -->

            <header class="topbar">

                <button
                    type="button"
                    class="menu-button"
                    id="menuButton"
                    aria-label="Abrir menú">
                    ☰
                </button>


                <div class="topbar-title">

                    <strong>
                        Expediente
                    </strong>

                    <span>
                        Ficha de gestión
                    </span>

                </div>


                <div class="topbar-expediente">

                    <?= htmlspecialchars(
                        $expediente['numero_expediente'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            </header>


            <!-- ==================================================
             CONTENIDO
        =================================================== -->

            <section class="content ver-expediente-content">


                <!-- ==================================================
                 ENCABEZADO EXPEDIENTE
            =================================================== -->

                <div class="expediente-header-card">

                    <div class="expediente-header-main">

                        <div class="expediente-icon">
                            📁
                        </div>


                        <div>

                            <span class="eyebrow">
                                EXPEDIENTE
                            </span>

                            <h1>

                                <?= htmlspecialchars(
                                    $expediente['numero_expediente'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </h1>


                            <p>

                                <?= htmlspecialchars(
                                    $expediente['tipo_tramite_nombre'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>

                        </div>

                    </div>


                    <div class="expediente-status">

                        <span
                            class="status-badge <?= $estadoClase ?>">

                            <span class="status-dot"></span>

                            <?= htmlspecialchars(
                                $expediente['estado_nombre'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </span>

                    </div>

                </div>


                <!-- ==================================================
                 ETAPA ACTUAL
            =================================================== -->

                <section class="info-card etapa-card">

                    <div class="section-heading">

                        <div class="section-heading-icon">
                            📌
                        </div>

                        <div>

                            <h2>
                                Etapa actual
                            </h2>

                            <p>
                                Situación actual del expediente.
                            </p>

                        </div>

                    </div>


                    <div class="etapa-current">

                        <div>

                            <span>
                                ETAPA
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $expediente['etapa_nombre'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </strong>

                        </div>


                        <div class="etapa-order">

                            <span>
                                ORDEN
                            </span>

                            <strong>

                                <?= (int) $expediente['etapa_orden'] ?>

                            </strong>

                        </div>

                    </div>


                    <?php if (
                        !empty($expediente['etapa_descripcion'])
                    ): ?>

                        <div class="info-description">

                            <?= nl2br(
                                htmlspecialchars(
                                    $expediente['etapa_descripcion'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            ) ?>

                        </div>

                    <?php endif; ?>

                </section>


                <!-- ==================================================
                 CLIENTE + GESTOR
            =================================================== -->

                <div class="two-column-grid">


                    <!-- CLIENTE -->

                    <section class="info-card">

                        <div class="section-heading">

                            <div class="section-heading-icon">
                                👤
                            </div>

                            <div>

                                <h2>
                                    Cliente
                                </h2>

                                <p>
                                    Cliente del despacho.
                                </p>

                            </div>

                        </div>


                        <div class="person-main">

                            <div class="person-avatar">
                                <?= htmlspecialchars(
                                    mb_strtoupper(
                                        mb_substr(
                                            $nombreCliente,
                                            0,
                                            1
                                        )
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </div>


                            <div>

                                <strong>

                                    <?= htmlspecialchars(
                                        $nombreCliente,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                                <span>

                                    Persona #<?= (int) $expediente['cliente_persona_id'] ?>

                                </span>

                            </div>

                        </div>


                        <div class="data-list">


                            <?php if (
                                !empty($expediente['cliente_curp'])
                            ): ?>

                                <div>

                                    <span>
                                        CURP
                                    </span>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $expediente['cliente_curp'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                !empty($expediente['cliente_rfc'])
                            ): ?>

                                <div>

                                    <span>
                                        RFC
                                    </span>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $expediente['cliente_rfc'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                !empty($expediente['cliente_telefono'])
                            ): ?>

                                <div>

                                    <span>
                                        Teléfono
                                    </span>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $expediente['cliente_telefono'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                !empty($expediente['cliente_correo'])
                            ): ?>

                                <div>

                                    <span>
                                        Correo
                                    </span>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $expediente['cliente_correo'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>

                        </div>


                        <button
                            type="button"
                            class="card-action"
                            disabled>
                            👤 Ver información completa
                        </button>

                    </section>


                    <!-- GESTOR -->

                    <section class="info-card">

                        <div class="section-heading">

                            <div class="section-heading-icon">
                                🧑‍💼
                            </div>

                            <div>

                                <h2>
                                    Gestor
                                </h2>

                                <p>
                                    Responsable operativo.
                                </p>

                            </div>

                        </div>


                        <div class="person-main">

                            <div class="person-avatar gestor-avatar">
                                🧑‍💼
                            </div>


                            <div>

                                <strong>

                                    <?= htmlspecialchars(
                                        $nombreGestor,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                                <span>
                                    <?= $nombreGestor === 'Sin asignar'
                                        ? 'Pendiente de asignación'
                                        : 'Gestor del expediente'
                                    ?>
                                </span>

                            </div>

                        </div>


                        <div class="gestor-empty">

                            <?php if (
                                $nombreGestor === 'Sin asignar'
                            ): ?>

                                <span>
                                    ⚠️
                                </span>

                                <p>
                                    Este expediente todavía
                                    no tiene un gestor asignado.
                                </p>

                            <?php else: ?>

                                <span>
                                    ✓
                                </span>

                                <p>
                                    El expediente tiene un gestor
                                    asignado.
                                </p>

                            <?php endif; ?>

                        </div>


                        <button
                            type="button"
                            class="card-action"
                            disabled>
                            🧑‍💼 Gestionar asignación
                        </button>

                    </section>

                </div>


                <!-- ==================================================
                 TIPO DE TRÁMITE
            =================================================== -->

                <section class="info-card">

                    <div class="section-heading">

                        <div class="section-heading-icon">
                            📋
                        </div>

                        <div>

                            <h2>
                                Tipo de trámite
                            </h2>

                            <p>
                                Información del acto jurídico.
                            </p>

                        </div>

                    </div>


                    <div class="tramite-box">

                        <strong>

                            <?= htmlspecialchars(
                                $expediente['tipo_tramite_nombre'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </strong>


                        <?php if (
                            !empty($expediente['tipo_tramite_descripcion'])
                        ): ?>

                            <p>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $expediente['tipo_tramite_descripcion'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                ) ?>

                            </p>

                        <?php endif; ?>

                    </div>

                </section>


                <!-- ==================================================
                 INFORMACIÓN GENERAL
            =================================================== -->

                <section class="info-card">

                    <div class="section-heading">

                        <div class="section-heading-icon">
                            ℹ️
                        </div>

                        <div>

                            <h2>
                                Información general
                            </h2>

                            <p>
                                Datos de control del expediente.
                            </p>

                        </div>

                    </div>


                    <div class="data-grid">


                        <div class="data-item">

                            <span>
                                ID interno
                            </span>

                            <strong>
                                #<?= (int) $expediente['id'] ?>
                            </strong>

                        </div>


                        <div class="data-item">

                            <span>
                                Fecha de creación
                            </span>

                            <strong>

                                <?= fechaMostrar(
                                    $expediente['fecha_creacion']
                                ) ?>

                            </strong>

                        </div>


                        <div class="data-item">

                            <span>
                                Última actualización
                            </span>

                            <strong>

                                <?= fechaMostrar(
                                    $expediente['fecha_actualizacion']
                                ) ?>

                            </strong>

                        </div>


                        <div class="data-item">

                            <span>
                                Creado por
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    trim(
                                        $expediente['creador_nombre']
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </strong>

                        </div>


                        <div class="data-item">

                            <span>
                                Finalización
                            </span>

                            <strong>

                                <?= fechaMostrar(
                                    $expediente['fecha_finalizacion']
                                ) ?>

                            </strong>

                        </div>


                        <div class="data-item">

                            <span>
                                Cancelación
                            </span>

                            <strong>

                                <?= fechaMostrar(
                                    $expediente['fecha_cancelacion']
                                ) ?>

                            </strong>

                        </div>

                    </div>

                </section>


                <!-- ==================================================
                 OBSERVACIONES
            =================================================== -->

                <section class="info-card">

                    <div class="section-heading">

                        <div class="section-heading-icon">
                            📝
                        </div>

                        <div>

                            <h2>
                                Observaciones
                            </h2>

                            <p>
                                Información adicional del expediente.
                            </p>

                        </div>

                    </div>


                    <?php if (
                        !empty(trim(
                            $expediente['observaciones'] ?? ''
                        ))
                    ): ?>

                        <div class="observaciones-box">

                            <?= nl2br(
                                htmlspecialchars(
                                    $expediente['observaciones'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            ) ?>

                        </div>

                    <?php else: ?>

                        <div class="empty-box">

                            <span>
                                📝
                            </span>

                            <p>
                                No hay observaciones registradas.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>


                <!-- ==================================================
     PARTICIPANTES
=================================================== -->

                <section class="info-card">

                    <div class="section-heading">

                        <div class="section-heading-icon">
                            👥
                        </div>

                        <div>

                            <h2>
                                Participantes
                            </h2>

                            <p>
                                Personas que intervienen en el acto jurídico.
                            </p>

                        </div>

                    </div>


                    <div
                        id="listaParticipantes"
                        class="participants-list">

                        <div class="empty-box">

                            <span>
                                👥
                            </span>

                            <p>
                                Cargando participantes...
                            </p>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="card-action participant-add-button"
                        id="btnAgregarParticipante">
                        ➕ Agregar participante
                    </button>

                </section>


                <!-- ==================================================
                 MÓDULOS FUTUROS
            =================================================== -->


                <section class="module-card">

                    <div class="module-icon">
                        📎
                    </div>

                    <div>

                        <h3>
                            Documentos
                        </h3>

                        <p>
                            Documentación requerida,
                            revisión y archivos adjuntos.
                        </p>

                    </div>

                    <button
                        type="button"
                        disabled>
                        Próximamente
                    </button>

                </section>


                <section class="module-card">

                    <div class="module-icon">
                        🏠
                    </div>

                    <div>

                        <h3>
                            Inmueble
                        </h3>

                        <p>
                            Datos del inmueble y
                            medidas y colindancias.
                        </p>

                    </div>

                    <button
                        type="button"
                        disabled>
                        Próximamente
                    </button>

                </section>


                <section class="module-card">

                    <div class="module-icon">
                        💰
                    </div>

                    <div>

                        <h3>
                            Finanzas
                        </h3>

                        <p>
                            Impuesto, pagos y control
                            financiero del expediente.
                        </p>

                    </div>

                    <button
                        type="button"
                        disabled>
                        Próximamente
                    </button>

                </section>

    </div>


    <!-- ==================================================
                 ACCIONES
            =================================================== -->

    <div class="bottom-actions">

        <a
            href="index.php"
            class="btn-secondary">
            ← Expedientes
        </a>


        <button
            type="button"
            class="btn-secondary"
            disabled>
            ✏️ Editar
        </button>


        <button
            type="button"
            class="btn-primary"
            disabled>
            🔄 Cambiar etapa
        </button>

    </div>


    </section>

    </main>

    </div>


    <!-- ==================================================
     ACCIONES RÁPIDAS MÓVIL
=================================================== -->

    <nav
        class="mobile-actions"
        aria-label="Acciones rápidas">

        <a
            href="index.php"
            class="mobile-action">

            <span class="mobile-action-icon">
                📁
            </span>

            <span>
                Expedientes
            </span>

        </a>


        <a
            href="#"
            class="mobile-action">

            <span class="mobile-action-icon">
                👥
            </span>

            <span>
                Personas
            </span>

        </a>


        <a
            href="#"
            class="mobile-action">

            <span class="mobile-action-icon">
                📎
            </span>

            <span>
                Docs
            </span>

        </a>


        <a
            href="#"
            class="mobile-action">

            <span class="mobile-action-icon">
                🔔
            </span>

            <span>
                Avisos
            </span>

        </a>

    </nav>


    <div
        class="sidebar-overlay"
        id="sidebarOverlay"></div>





    <div
        class="modal-overlay"
        id="modalParticipante"
        hidden>

        <div class="persona-modal participante-modal">

            <div class="persona-modal-header">

                <div>

                    <span>
                        EXPEDIENTE
                    </span>

                    <h2>
                        Agregar participante
                    </h2>

                    <p>
                        Selecciona la persona y su función jurídica.
                    </p>

                </div>

                <button
                    type="button"
                    class="modal-close"
                    id="cerrarModalParticipante">
                    ✕
                </button>

            </div>


            <form
                id="formParticipante">

                <input
                    type="hidden"
                    name="expediente_id"
                    value="<?= (int) $expediente['id'] ?>">

                <input
                    type="hidden"
                    name="persona_id"
                    id="participantePersonaId">


                <!-- PERSONA -->

                <div class="form-group">

                    <label>
                        Persona
                    </label>

                    <input
                        type="search"
                        id="buscarParticipante"
                        placeholder="Buscar nombre, CURP o RFC..."
                        autocomplete="off">

                    <div
                        id="resultadosParticipante"
                        class="participant-search-results"></div>

                    <button
                        type="button"
                        class="create-person-button"
                        id="btnCrearPersonaParticipante">
                        ➕ No encuentro la persona — crear nueva
                    </button>

                    <div
                        id="formNuevaPersonaParticipante"
                        class="new-person-form"
                        hidden>

                        <div class="new-person-header">

                            <div>

                                <strong>
                                    Nueva persona
                                </strong>

                                <span>
                                    Registra los datos de la persona participante.
                                </span>

                            </div>

                            <button
                                type="button"
                                id="cancelarNuevaPersona"
                                class="new-person-close">
                                ✕
                            </button>

                        </div>


                        <div class="new-person-grid">

                            <!-- NOMBRE -->

                            <div class="form-group">

                                <label for="nuevaPersonaNombre">
                                    Nombre *
                                </label>

                                <input
                                    type="text"
                                    id="nuevaPersonaNombre"
                                    maxlength="100"
                                    autocomplete="off">

                            </div>


                            <!-- APELLIDO PATERNO -->

                            <div class="form-group">

                                <label for="nuevaPersonaApellidoPaterno">
                                    Apellido paterno *
                                </label>

                                <input
                                    type="text"
                                    id="nuevaPersonaApellidoPaterno"
                                    maxlength="100"
                                    autocomplete="off">

                            </div>


                            <!-- APELLIDO MATERNO -->

                            <div class="form-group">

                                <label for="nuevaPersonaApellidoMaterno">
                                    Apellido materno
                                </label>

                                <input
                                    type="text"
                                    id="nuevaPersonaApellidoMaterno"
                                    maxlength="100"
                                    autocomplete="off">

                            </div>


                            <!-- FECHA DE NACIMIENTO -->

                            <div class="form-group">

                                <label for="nuevaPersonaFechaNacimiento">
                                    Fecha de nacimiento
                                </label>

                                <input
                                    type="date"
                                    id="nuevaPersonaFechaNacimiento">

                            </div>


                            <!-- CURP -->

                            <div class="form-group">

                                <label for="nuevaPersonaCurp">
                                    CURP
                                </label>

                                <input
                                    type="text"
                                    id="nuevaPersonaCurp"
                                    maxlength="18"
                                    autocomplete="off"
                                    style="text-transform: uppercase;">

                            </div>


                            <!-- RFC -->

                            <div class="form-group">

                                <label for="nuevaPersonaRfc">
                                    RFC
                                </label>

                                <input
                                    type="text"
                                    id="nuevaPersonaRfc"
                                    maxlength="13"
                                    autocomplete="off"
                                    style="text-transform: uppercase;">

                            </div>


                            <!-- TELÉFONO -->

                            <div class="form-group">

                                <label for="nuevaPersonaTelefono">
                                    Teléfono
                                </label>

                                <input
                                    type="tel"
                                    id="nuevaPersonaTelefono"
                                    maxlength="20"
                                    autocomplete="off">

                            </div>


                            <!-- CORREO -->

                            <div class="form-group">

                                <label for="nuevaPersonaCorreo">
                                    Correo electrónico
                                </label>

                                <input
                                    type="email"
                                    id="nuevaPersonaCorreo"
                                    maxlength="150"
                                    autocomplete="off">

                            </div>


                            <!-- DOMICILIO -->

                            <div class="form-group new-person-full">

                                <label for="nuevaPersonaDomicilio">
                                    Domicilio
                                </label>

                                <textarea
                                    id="nuevaPersonaDomicilio"
                                    rows="2"
                                    placeholder="Domicilio de la persona..."></textarea>

                            </div>


                            <!-- OBSERVACIONES -->

                            <div class="form-group new-person-full">

                                <label for="nuevaPersonaObservaciones">
                                    Observaciones
                                </label>

                                <textarea
                                    id="nuevaPersonaObservaciones"
                                    rows="2"
                                    placeholder="Observaciones..."></textarea>

                            </div>

                        </div>


                        <!-- ERROR -->

                        <div
                            id="nuevaPersonaError"
                            class="persona-error"
                            hidden></div>


                        <!-- GUARDAR -->

                        <button
                            type="button"
                            id="guardarNuevaPersona"
                            class="btn-primary new-person-save">
                            👤 Crear y seleccionar persona
                        </button>

                    </div>

                </div>


                <div
                    id="participanteSeleccionado"
                    class="selected-client"
                    hidden>

                    <div>

                        <span>
                            Persona seleccionada
                        </span>

                        <strong
                            id="participanteNombre"></strong>

                    </div>

                    <button
                        type="button"
                        class="remove-client"
                        id="cambiarParticipante">
                        Cambiar
                    </button>

                </div>


                <!-- ROL -->

                <div class="form-group">

                    <label for="rolParticipante">

                        Rol dentro del acto jurídico

                    </label>

                    <select
                        name="rol_participante_id"
                        id="rolParticipante"
                        required>

                        <option value="">
                            Seleccionar rol...
                        </option>

                    </select>

                </div>


                <!-- REPRESENTADO -->

                <div
                    class="form-group"
                    id="grupoRepresentado"
                    hidden>

                    <label for="personaRepresentada">

                        Persona representada

                    </label>

                    <select
                        name="persona_representada_id"
                        id="personaRepresentada">

                        <option value="">
                            Seleccionar persona...
                        </option>

                    </select>

                </div>


                <!-- OBSERVACIONES -->

                <div class="form-group">

                    <label for="observacionesParticipante">

                        Observaciones

                    </label>

                    <textarea
                        name="observaciones"
                        id="observacionesParticipante"
                        rows="3"
                        placeholder="Observaciones sobre su participación..."></textarea>

                </div>


                <div
                    id="participanteError"
                    class="persona-error"
                    hidden></div>


                <div class="persona-modal-actions">

                    <button
                        type="button"
                        class="btn-secondary"
                        id="cancelarParticipante">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                        id="guardarParticipante">
                        👥 Agregar participante
                    </button>

                </div>

            </form>

        </div>

    </div>
    <div
        class="modal-overlay"
        id="modalEditarParticipante"
        hidden>

        <div class="persona-modal">

            <div class="persona-modal-header">

                <div>

                    <span>
                        PARTICIPANTE
                    </span>

                    <h2>
                        Editar participante
                    </h2>

                    <p>
                        Modifica la participación dentro del expediente.
                    </p>

                </div>

                <button
                    type="button"
                    class="modal-close"
                    id="cerrarModalEditar">
                    ✕
                </button>

            </div>


            <form id="formEditarParticipante">

                <input
                    type="hidden"
                    name="id"
                    id="editarParticipanteId">


                <!-- PERSONA -->

                <div class="form-group">

                    <label>
                        Persona
                    </label>

                    <input
                        type="search"
                        id="buscarEditarParticipante"
                        placeholder="Buscar nombre, CURP o RFC..."
                        autocomplete="off">

                    <div
                        id="resultadosEditarParticipante"
                        class="participant-search-results"></div>

                </div>


                <div
                    id="editarPersonaSeleccionada"
                    class="selected-client"
                    hidden>

                    <div>

                        <span>
                            Persona seleccionada
                        </span>

                        <strong
                            id="editarPersonaNombre"></strong>

                    </div>

                    <button
                        type="button"
                        class="remove-client"
                        id="cambiarEditarPersona">
                        Cambiar
                    </button>

                </div>


                <!-- ROL -->

                <div class="form-group">

                    <label>
                        Rol dentro del acto jurídico
                    </label>

                    <select
                        id="editarRolParticipante"
                        required>

                        <option value="">
                            Seleccionar rol...
                        </option>

                    </select>

                </div>


                <!-- REPRESENTADO -->

                <div
                    class="form-group"
                    id="grupoEditarRepresentado"
                    hidden>

                    <label>
                        Persona representada
                    </label>

                    <select
                        id="editarPersonaRepresentada">

                        <option value="">
                            Seleccionar persona...
                        </option>

                    </select>

                </div>


                <!-- OBSERVACIONES -->

                <div class="form-group">

                    <label>
                        Observaciones
                    </label>

                    <textarea
                        id="editarObservaciones"
                        rows="3"></textarea>

                </div>


                <div
                    id="editarParticipanteError"
                    class="persona-error"
                    hidden></div>


                <div class="persona-modal-actions">

                    <button
                        type="button"
                        class="btn-secondary"
                        id="cancelarEditarParticipante">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                        id="guardarEditarParticipante">
                        💾 Guardar cambios
                    </button>

                </div>

            </form>

            <div
                class="modal-overlay"
                id="modalDatosPersona"
                hidden>

                <div class="persona-modal">

                    <!-- ENCABEZADO -->

                    <div class="persona-modal-header">

                        <div>

                            <span>
                                PERSONA
                            </span>

                            <h2>
                                Datos personales
                            </h2>

                            <p>
                                Consulta y actualiza los datos de la persona.
                            </p>

                        </div>


                        <button
                            type="button"
                            class="modal-close"
                            id="cerrarModalDatosPersona">
                            ✕
                        </button>

                    </div>


                    <!-- FORMULARIO -->

                    <form
                        id="formDatosPersona">

                        <input
                            type="hidden"
                            id="datosPersonaId">


                        <!-- NOMBRE -->

                        <div class="form-group">

                            <label for="datosPersonaNombre">
                                Nombre
                            </label>

                            <input
                                type="text"
                                id="datosPersonaNombre"
                                maxlength="100"
                                required>

                        </div>


                        <!-- APELLIDOS -->

                        <div
                            class="form-grid-2">

                            <div class="form-group">

                                <label for="datosPersonaApellidoPaterno">
                                    Apellido paterno
                                </label>

                                <input
                                    type="text"
                                    id="datosPersonaApellidoPaterno"
                                    maxlength="100"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="datosPersonaApellidoMaterno">
                                    Apellido materno
                                </label>

                                <input
                                    type="text"
                                    id="datosPersonaApellidoMaterno"
                                    maxlength="100">

                            </div>

                        </div>


                        <!-- FECHA Y EDAD -->

                        <div
                            class="form-grid-2">

                            <div class="form-group">

                                <label for="datosPersonaFechaNacimiento">
                                    Fecha de nacimiento
                                </label>

                                <input
                                    type="date"
                                    id="datosPersonaFechaNacimiento">

                            </div>


                            <div class="form-group">

                                <label>
                                    Edad
                                </label>

                                <div
                                    class="edad-calculada"
                                    id="datosPersonaEdad">
                                    —
                                </div>

                            </div>

                        </div>


                        <!-- ESTADO CIVIL -->

                        <div class="form-group">

                            <label for="datosPersonaEstadoCivil">
                                Estado civil
                            </label>

                            <select
                                id="datosPersonaEstadoCivil">

                                <option value="">
                                    Seleccionar estado civil...
                                </option>

                            </select>

                        </div>


                        <!-- CURP / RFC -->

                        <div
                            class="form-grid-2">

                            <div class="form-group">

                                <label for="datosPersonaCurp">
                                    CURP
                                </label>

                                <input
                                    type="text"
                                    id="datosPersonaCurp"
                                    maxlength="18"
                                    autocomplete="off">

                            </div>


                            <div class="form-group">

                                <label for="datosPersonaRfc">
                                    RFC
                                </label>

                                <input
                                    type="text"
                                    id="datosPersonaRfc"
                                    maxlength="13"
                                    autocomplete="off">

                            </div>

                        </div>


                        <!-- TELÉFONO / CORREO -->

                        <div
                            class="form-grid-2">

                            <div class="form-group">

                                <label for="datosPersonaTelefono">
                                    Teléfono
                                </label>

                                <input
                                    type="tel"
                                    id="datosPersonaTelefono"
                                    maxlength="20">

                            </div>


                            <div class="form-group">

                                <label for="datosPersonaCorreo">
                                    Correo electrónico
                                </label>

                                <input
                                    type="email"
                                    id="datosPersonaCorreo"
                                    maxlength="150">

                            </div>

                        </div>


                        <!-- DOMICILIO -->

                        <div class="form-group">

                            <label for="datosPersonaDomicilio">
                                Domicilio
                            </label>

                            <textarea
                                id="datosPersonaDomicilio"
                                rows="3"></textarea>

                        </div>


                        <!-- OBSERVACIONES -->

                        <div class="form-group">

                            <label for="datosPersonaObservaciones">
                                Observaciones
                            </label>

                            <textarea
                                id="datosPersonaObservaciones"
                                rows="3"></textarea>

                        </div>


                        <!-- ERROR -->

                        <div
                            id="datosPersonaError"
                            class="persona-error"
                            hidden></div>


                        <!-- ACCIONES -->

                        <div class="persona-modal-actions">

                            <button
                                type="button"
                                class="btn-secondary"
                                id="cancelarDatosPersona">
                                Cancelar
                            </button>


                            <button
                                type="submit"
                                class="btn-primary"
                                id="guardarDatosPersona">
                                💾 Guardar cambios
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>
    <script src="../js/participantes.js"></script>
    <script src="../js/dashboard.js"></script>
</body>

</html>
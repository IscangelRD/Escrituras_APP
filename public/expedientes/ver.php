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
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="theme-color"
        content="#111827"
    >

    <title>

        <?= htmlspecialchars(
            $expediente['numero_expediente'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </title>


    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="../css/expedientes.css"
    >

    <link
        rel="stylesheet"
        href="../css/ver-expediente.css"
    >

</head>


<body>

<div class="app">


    <!-- ==================================================
         SIDEBAR
    =================================================== -->

    <aside
        class="sidebar"
        id="sidebar"
    >

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
                class="menu-item"
            >
                <span>🏠</span>
                <span>Dashboard</span>
            </a>


            <a
                href="index.php"
                class="menu-item active"
            >
                <span>📁</span>
                <span>Expedientes</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>👥</span>
                <span>Personas</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>🏠</span>
                <span>Inmuebles</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>📎</span>
                <span>Documentos</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>🔎</span>
                <span>Revisiones</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>📝</span>
                <span>Observaciones</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>💰</span>
                <span>Finanzas</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>🏛️</span>
                <span>Impuestos</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>🔔</span>
                <span>Notificaciones</span>
            </a>


            <a
                href="#"
                class="menu-item"
            >
                <span>🏆</span>
                <span>Logros</span>
            </a>

        </nav>


        <div class="sidebar-footer">

            <a
                href="../logout.php"
                class="logout-button"
            >
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
                aria-label="Abrir menú"
            >
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
                                $expediente[
                                    'numero_expediente'
                                ],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </h1>


                        <p>

                            <?= htmlspecialchars(
                                $expediente[
                                    'tipo_tramite_nombre'
                                ],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                    </div>

                </div>


                <div class="expediente-status">

                    <span
                        class="status-badge <?= $estadoClase ?>"
                    >

                        <span class="status-dot"></span>

                        <?= htmlspecialchars(
                            $expediente[
                                'estado_nombre'
                            ],
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
                                $expediente[
                                    'etapa_nombre'
                                ],
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

                            <?= (int) $expediente[
                                'etapa_orden'
                            ] ?>

                        </strong>

                    </div>

                </div>


                <?php if (
                    !empty(
                        $expediente[
                            'etapa_descripcion'
                        ]
                    )
                ): ?>

                    <div class="info-description">

                        <?= nl2br(
                            htmlspecialchars(
                                $expediente[
                                    'etapa_descripcion'
                                ],
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

                                Persona #<?= (int) $expediente[
                                    'cliente_persona_id'
                                ] ?>

                            </span>

                        </div>

                    </div>


                    <div class="data-list">


                        <?php if (
                            !empty(
                                $expediente[
                                    'cliente_curp'
                                ]
                            )
                        ): ?>

                            <div>

                                <span>
                                    CURP
                                </span>

                                <strong>

                                    <?= htmlspecialchars(
                                        $expediente[
                                            'cliente_curp'
                                        ],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>


                        <?php if (
                            !empty(
                                $expediente[
                                    'cliente_rfc'
                                ]
                            )
                        ): ?>

                            <div>

                                <span>
                                    RFC
                                </span>

                                <strong>

                                    <?= htmlspecialchars(
                                        $expediente[
                                            'cliente_rfc'
                                        ],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>


                        <?php if (
                            !empty(
                                $expediente[
                                    'cliente_telefono'
                                ]
                            )
                        ): ?>

                            <div>

                                <span>
                                    Teléfono
                                </span>

                                <strong>

                                    <?= htmlspecialchars(
                                        $expediente[
                                            'cliente_telefono'
                                        ],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>


                        <?php if (
                            !empty(
                                $expediente[
                                    'cliente_correo'
                                ]
                            )
                        ): ?>

                            <div>

                                <span>
                                    Correo
                                </span>

                                <strong>

                                    <?= htmlspecialchars(
                                        $expediente[
                                            'cliente_correo'
                                        ],
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
                        disabled
                    >
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
                        disabled
                    >
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
                            $expediente[
                                'tipo_tramite_nombre'
                            ],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </strong>


                    <?php if (
                        !empty(
                            $expediente[
                                'tipo_tramite_descripcion'
                            ]
                        )
                    ): ?>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $expediente[
                                        'tipo_tramite_descripcion'
                                    ],
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
                                $expediente[
                                    'fecha_creacion'
                                ]
                            ) ?>

                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            Última actualización
                        </span>

                        <strong>

                            <?= fechaMostrar(
                                $expediente[
                                    'fecha_actualizacion'
                                ]
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
                                    $expediente[
                                        'creador_nombre'
                                    ]
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
                                $expediente[
                                    'fecha_finalizacion'
                                ]
                            ) ?>

                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            Cancelación
                        </span>

                        <strong>

                            <?= fechaMostrar(
                                $expediente[
                                    'fecha_cancelacion'
                                ]
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
                    !empty(
                        trim(
                            $expediente[
                                'observaciones'
                            ] ?? ''
                        )
                    )
                ): ?>

                    <div class="observaciones-box">

                        <?= nl2br(
                            htmlspecialchars(
                                $expediente[
                                    'observaciones'
                                ],
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
                 MÓDULOS FUTUROS
            =================================================== -->

            <div class="modules-grid">


                <section class="module-card">

                    <div class="module-icon">
                        👥
                    </div>

                    <div>

                        <h3>
                            Participantes
                        </h3>

                        <p>
                            Vendedores, compradores,
                            donantes, herederos,
                            apoderados y demás participantes.
                        </p>

                    </div>

                    <button
                        type="button"
                        disabled
                    >
                        Próximamente
                    </button>

                </section>


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
                        disabled
                    >
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
                        disabled
                    >
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
                        disabled
                    >
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
                    class="btn-secondary"
                >
                    ← Expedientes
                </a>


                <button
                    type="button"
                    class="btn-secondary"
                    disabled
                >
                    ✏️ Editar
                </button>


                <button
                    type="button"
                    class="btn-primary"
                    disabled
                >
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
    aria-label="Acciones rápidas"
>

    <a
        href="index.php"
        class="mobile-action"
    >

        <span class="mobile-action-icon">
            📁
        </span>

        <span>
            Expedientes
        </span>

    </a>


    <a
        href="#"
        class="mobile-action"
    >

        <span class="mobile-action-icon">
            👥
        </span>

        <span>
            Personas
        </span>

    </a>


    <a
        href="#"
        class="mobile-action"
    >

        <span class="mobile-action-icon">
            📎
        </span>

        <span>
            Docs
        </span>

    </a>


    <a
        href="#"
        class="mobile-action"
    >

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
    id="sidebarOverlay"
></div>


<script src="../js/dashboard.js"></script>

</body>

</html>
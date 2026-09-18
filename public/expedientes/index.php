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
| Configuración
|--------------------------------------------------------------------------
*/

$porPagina = 10;

$pagina = isset($_GET['pagina'])
    ? max(1, (int) $_GET['pagina'])
    : 1;

$busqueda = trim(
    $_GET['buscar'] ?? ''
);

$offset = ($pagina - 1) * $porPagina;


/*
|--------------------------------------------------------------------------
| Construcción de filtros
|--------------------------------------------------------------------------
*/

$where = [
    'e.activo = 1',
    'e.eliminado = 0'
];

$params = [];


/*
|--------------------------------------------------------------------------
| Buscador
|--------------------------------------------------------------------------
*/

if ($busqueda !== '') {

    $where[] = "
        (
            e.numero_expediente LIKE :buscar_expediente
            OR CONCAT(
                c.nombre,
                ' ',
                c.apellido_paterno,
                ' ',
                COALESCE(c.apellido_materno, '')
            ) LIKE :buscar_cliente
            OR tt.nombre LIKE :buscar_tramite
            OR EXISTS (
                SELECT 1
                FROM expediente_personas ep_busqueda
                INNER JOIN personas p_busqueda
                    ON p_busqueda.id = ep_busqueda.persona_id
                WHERE
                    ep_busqueda.expediente_id = e.id
                    AND ep_busqueda.activo = 1
                    AND CONCAT(
                        p_busqueda.nombre,
                        ' ',
                        p_busqueda.apellido_paterno,
                        ' ',
                        COALESCE(
                            p_busqueda.apellido_materno,
                            ''
                        )
                    ) LIKE :buscar_participante
            )
        )
    ";

    $textoBusqueda = '%' . $busqueda . '%';

    $params[':buscar_expediente'] = $textoBusqueda;
    $params[':buscar_cliente'] = $textoBusqueda;
    $params[':buscar_tramite'] = $textoBusqueda;
    $params[':buscar_participante'] = $textoBusqueda;
}


$whereSql = implode(
    ' AND ',
    $where
);


/*
|--------------------------------------------------------------------------
| Total de expedientes
|--------------------------------------------------------------------------
*/

$sqlTotal = "
    SELECT COUNT(*)
    FROM expedientes e

    INNER JOIN personas c
        ON c.id = e.cliente_id

    INNER JOIN tipos_tramite tt
        ON tt.id = e.tipo_tramite_id

    WHERE {$whereSql}
";

$stmtTotal = $pdo->prepare($sqlTotal);

$stmtTotal->execute($params);

$totalExpedientes = (int) $stmtTotal->fetchColumn();

$totalPaginas = max(
    1,
    (int) ceil(
        $totalExpedientes / $porPagina
    )
);


/*
|--------------------------------------------------------------------------
| Ajustar página
|--------------------------------------------------------------------------
*/

if ($pagina > $totalPaginas) {

    $pagina = $totalPaginas;

    $offset = ($pagina - 1) * $porPagina;
}


/*
|--------------------------------------------------------------------------
| Obtener expedientes
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        e.id,
        e.numero_expediente,

        e.cliente_id,
        e.gestor_id,

        e.tipo_tramite_id,
        e.estado_id,
        e.etapa_actual_id,

        e.fecha_creacion,
        e.fecha_actualizacion,

        c.nombre AS cliente_nombre,
        c.apellido_paterno AS cliente_apellido_paterno,
        c.apellido_materno AS cliente_apellido_materno,

        tt.nombre AS tramite_nombre,

        ee.nombre AS estado_nombre,

        et.nombre AS etapa_nombre,
        et.orden AS etapa_orden,

        CONCAT(
            COALESCE(g.nombre, ''),
            ' ',
            COALESCE(g.apellido_paterno, ''),
            ' ',
            COALESCE(g.apellido_materno, '')
        ) AS gestor_nombre

    FROM expedientes e

    INNER JOIN personas c
        ON c.id = e.cliente_id

    INNER JOIN tipos_tramite tt
        ON tt.id = e.tipo_tramite_id

    INNER JOIN estados_expediente ee
        ON ee.id = e.estado_id

    INNER JOIN etapas et
        ON et.id = e.etapa_actual_id

    LEFT JOIN usuarios g
        ON g.id = e.gestor_id

    WHERE {$whereSql}

    ORDER BY
        e.fecha_actualizacion DESC,
        e.id DESC

    LIMIT :limite
    OFFSET :offset
";

$stmt = $pdo->prepare($sql);

foreach ($params as $clave => $valor) {

    $stmt->bindValue(
        $clave,
        $valor,
        PDO::PARAM_STR
    );
}

$stmt->bindValue(
    ':limite',
    $porPagina,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':offset',
    $offset,
    PDO::PARAM_INT
);

$stmt->execute();

$expedientes = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Nombre completo
|--------------------------------------------------------------------------
*/

function nombreCompleto(
    string $nombre,
    ?string $apellidoPaterno,
    ?string $apellidoMaterno
): string {

    return trim(
        $nombre . ' ' .
        $apellidoPaterno . ' ' .
        ($apellidoMaterno ?? '')
    );
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
        Expedientes | <?= htmlspecialchars(APP_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="../css/expedientes.css"
    >

</head>

<body>

<div class="app">


    <!-- =========================
         SIDEBAR
    ========================== -->

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

            <a href="#" class="menu-item">
                <span>👥</span>
                <span>Personas</span>
            </a>

            <a href="#" class="menu-item">
                <span>🏠</span>
                <span>Inmuebles</span>
            </a>

            <a href="#" class="menu-item">
                <span>📎</span>
                <span>Documentos</span>
            </a>

            <a href="#" class="menu-item">
                <span>🔎</span>
                <span>Revisiones</span>
            </a>

            <a href="#" class="menu-item">
                <span>📝</span>
                <span>Observaciones</span>
            </a>

            <a href="#" class="menu-item">
                <span>💰</span>
                <span>Finanzas</span>
            </a>

            <a href="#" class="menu-item">
                <span>🏛️</span>
                <span>Impuestos</span>
            </a>

            <a href="#" class="menu-item">
                <span>🔔</span>
                <span>Notificaciones</span>
            </a>

            <a href="#" class="menu-item">
                <span>🏆</span>
                <span>Logros</span>
            </a>


            <?php if (rolId() === 1): ?>

                <div class="menu-section">
                    Administración
                </div>

                <a href="#" class="menu-item">
                    <span>👤</span>
                    <span>Usuarios</span>
                </a>

                <a href="#" class="menu-item">
                    <span>⚙️</span>
                    <span>Configuración</span>
                </a>

                <a href="#" class="menu-item">
                    <span>📜</span>
                    <span>Historial</span>
                </a>

            <?php endif; ?>

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


    <!-- =========================
         CONTENIDO
    ========================== -->

    <main class="main">


        <!-- =====================
             TOPBAR
        ====================== -->

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
                    Expedientes
                </strong>

                <span>
                    Control y seguimiento
                </span>

            </div>


            <div class="user-area">

                <div class="user-avatar">

                    <?= strtoupper(
                        mb_substr(
                            usuarioNombre(),
                            0,
                            1
                        )
                    ) ?>

                </div>

                <div class="user-info">

                    <strong>
                        <?= htmlspecialchars(
                            usuarioNombre(),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>

                    <small>
                        <?= htmlspecialchars(
                            $_SESSION['rol_nombre'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </small>

                </div>

            </div>

        </header>


        <!-- =====================
             CONTENIDO
        ====================== -->

        <section class="content expedientes-content">


            <!-- =====================
                 ENCABEZADO
            ====================== -->

            <div class="page-heading">

                <div>

                    <span>
                        CONTROL DE EXPEDIENTES
                    </span>

                    <h1>
                        Expedientes
                    </h1>

                    <p>
                        <?= number_format(
                            $totalExpedientes
                        ) ?>

                        expediente<?= 
                            $totalExpedientes === 1
                                ? ''
                                : 's'
                        ?>
                        encontrado<?= 
                            $totalExpedientes === 1
                                ? ''
                                : 's'
                        ?>
                    </p>

                </div>


                <?php if (
                    tienePermiso(
                        $pdo,
                        'expedientes',
                        'crear'
                    )
                ): ?>

                    <a
                        href="crear.php"
                        class="btn-primary"
                    >
                        ➕ Nuevo expediente
                    </a>

                <?php endif; ?>

            </div>


            <!-- =====================
                 BUSCADOR
            ====================== -->

            <div class="search-card">

                <form
                    method="GET"
                    action="index.php"
                    class="search-form"
                >

                    <div class="search-input-wrapper">

                        <span class="search-icon">
                            🔎
                        </span>

                        <input
                            type="search"
                            name="buscar"
                            id="buscar"
                            value="<?= htmlspecialchars(
                                $busqueda,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Buscar por expediente, cliente, participante o trámite..."
                            autocomplete="off"
                        >

                        <?php if ($busqueda !== ''): ?>

                            <a
                                href="index.php"
                                class="clear-search"
                                title="Limpiar búsqueda"
                            >
                                ✕
                            </a>

                        <?php endif; ?>

                    </div>

                    <button
                        type="submit"
                        class="btn-search"
                    >
                        Buscar
                    </button>

                </form>

            </div>


            <!-- =====================
                 LISTADO ESCRITORIO
            ====================== -->

            <div class="desktop-table-card">

                <div class="table-wrapper">

                    <table class="expedientes-table">

                        <thead>

                            <tr>

                                <th>
                                    Expediente
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th>
                                    Trámite
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Etapa
                                </th>

                                <th>
                                    Gestor
                                </th>

                                <th>
                                    Actualización
                                </th>

                                <th>
                                    Acción
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (!$expedientes): ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="empty-cell"
                                >

                                    <div class="empty-state">

                                        <div>
                                            📁
                                        </div>

                                        <strong>
                                            No hay expedientes
                                        </strong>

                                        <span>
                                            No encontramos expedientes
                                            con los criterios indicados.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach (
                                $expedientes
                                as $expediente
                            ): ?>

                                <tr>

                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $expediente[
                                                    'numero_expediente'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            nombreCompleto(
                                                $expediente[
                                                    'cliente_nombre'
                                                ],
                                                $expediente[
                                                    'cliente_apellido_paterno'
                                                ],
                                                $expediente[
                                                    'cliente_apellido_materno'
                                                ]
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $expediente[
                                                'tramite_nombre'
                                            ],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <td>

                                        <span class="badge badge-status">

                                            <?= htmlspecialchars(
                                                $expediente[
                                                    'estado_nombre'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="badge badge-stage">

                                            <?= htmlspecialchars(
                                                $expediente[
                                                    'etapa_nombre'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?php

                                        $gestor =
                                            trim(
                                                $expediente[
                                                    'gestor_nombre'
                                                ]
                                            );

                                        ?>

                                        <?= $gestor !== ''
                                            ? htmlspecialchars(
                                                $gestor,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            : 'Sin asignar'
                                        ?>

                                    </td>


                                    <td>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $expediente[
                                                    'fecha_actualizacion'
                                                ]
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <a
                                            href="ver.php?id=<?= (int) $expediente['id'] ?>"
                                            class="btn-view"
                                        >
                                            Ver
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- =====================
                 LISTADO MÓVIL
            ====================== -->

            <div class="mobile-expedientes">

                <?php if (!$expedientes): ?>

                    <div class="mobile-empty">

                        <div>
                            📁
                        </div>

                        <strong>
                            No hay expedientes
                        </strong>

                        <span>
                            No encontramos resultados.
                        </span>

                    </div>

                <?php else: ?>

                    <?php foreach (
                        $expedientes
                        as $expediente
                    ): ?>

                        <article
                            class="expediente-card"
                        >

                            <div class="expediente-card-top">

                                <strong>

                                    <?= htmlspecialchars(
                                        $expediente[
                                            'numero_expediente'
                                        ],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                                <span class="badge badge-status">

                                    <?= htmlspecialchars(
                                        $expediente[
                                            'estado_nombre'
                                        ],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </div>


                            <div class="expediente-card-client">

                                <?= htmlspecialchars(
                                    nombreCompleto(
                                        $expediente[
                                            'cliente_nombre'
                                        ],
                                        $expediente[
                                            'cliente_apellido_paterno'
                                        ],
                                        $expediente[
                                            'cliente_apellido_materno'
                                        ]
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </div>


                            <div class="expediente-card-info">

                                <div>

                                    <span>
                                        Trámite
                                    </span>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $expediente[
                                                'tramite_nombre'
                                            ],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Etapa
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


                                <div>

                                    <span>
                                        Gestor
                                    </span>

                                    <strong>

                                        <?= $gestor !== ''
                                            ? htmlspecialchars(
                                                $gestor,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            : 'Sin asignar'
                                        ?>

                                    </strong>

                                </div>

                            </div>


                            <div class="expediente-card-footer">

                                <small>

                                    Actualizado:

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $expediente[
                                                'fecha_actualizacion'
                                            ]
                                        )
                                    ) ?>

                                </small>


                                <a
                                    href="ver.php?id=<?= (int) $expediente['id'] ?>"
                                    class="btn-view"
                                >
                                    Ver expediente →
                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>


            <!-- =====================
                 PAGINACIÓN
            ====================== -->

            <?php if ($totalPaginas > 1): ?>

                <div class="pagination">

                    <?php if ($pagina > 1): ?>

                        <a
                            href="?<?= http_build_query([
                                'buscar' => $busqueda,
                                'pagina' => $pagina - 1
                            ]) ?>"
                            class="page-button"
                        >
                            ‹
                        </a>

                    <?php endif; ?>


                    <?php

                    $inicio = max(
                        1,
                        $pagina - 2
                    );

                    $fin = min(
                        $totalPaginas,
                        $pagina + 2
                    );

                    ?>


                    <?php for (
                        $i = $inicio;
                        $i <= $fin;
                        $i++
                    ): ?>

                        <a
                            href="?<?= http_build_query([
                                'buscar' => $busqueda,
                                'pagina' => $i
                            ]) ?>"
                            class="page-button
                                <?= $i === $pagina
                                    ? 'active'
                                    : ''
                                ?>"
                        >
                            <?= $i ?>
                        </a>

                    <?php endfor; ?>


                    <?php if ($pagina < $totalPaginas): ?>

                        <a
                            href="?<?= http_build_query([
                                'buscar' => $busqueda,
                                'pagina' => $pagina + 1
                            ]) ?>"
                            class="page-button"
                        >
                            ›
                        </a>

                    <?php endif; ?>

                </div>

            <?php endif; ?>


        </section>

    </main>

</div>


<!-- =========================
     ACCIONES RÁPIDAS MÓVIL
========================== -->

<nav
    class="mobile-actions"
    aria-label="Acciones rápidas"
>

    <?php if (
        tienePermiso(
            $pdo,
            'expedientes',
            'crear'
        )
    ): ?>

        <a
            href="crear.php"
            class="mobile-action"
        >
            <span class="mobile-action-icon">
                ➕
            </span>

            <span>
                Nuevo
            </span>
        </a>

    <?php endif; ?>


    <a
        href="#buscar"
        class="mobile-action"
        id="mobileSearch"
    >

        <span class="mobile-action-icon">
            🔎
        </span>

        <span>
            Buscar
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
            Subir
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

<script src="../js/expedientes.js"></script>

</body>

</html>
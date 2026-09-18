<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/Helpers/permisos.php';

exigirPermiso(
    $pdo,
    'expedientes',
    'crear'
);


/*
|--------------------------------------------------------------------------
| Tipos de trámite
|--------------------------------------------------------------------------
*/

$stmtTipos = $pdo->query("
    SELECT
        id,
        nombre,
        descripcion
    FROM tipos_tramite
    WHERE activo = 1
    ORDER BY nombre ASC
");

$tiposTramite = $stmtTipos->fetchAll();


/*
|--------------------------------------------------------------------------
| Estados
|--------------------------------------------------------------------------
*/

$stmtEstados = $pdo->query("
    SELECT
        id,
        nombre,
        descripcion
    FROM estados_expediente
    WHERE activo = 1
    ORDER BY id ASC
");

$estados = $stmtEstados->fetchAll();


/*
|--------------------------------------------------------------------------
| Etapas
|--------------------------------------------------------------------------
| Inicialmente mostramos las etapas activas.
| Al seleccionar trámite, JavaScript las filtrará.
|--------------------------------------------------------------------------
*/

$stmtEtapas = $pdo->query("
    SELECT
        id,
        nombre,
        descripcion,
        orden
    FROM etapas
    WHERE activo = 1
    ORDER BY orden ASC, id ASC
");

$etapas = $stmtEtapas->fetchAll();


/*
|--------------------------------------------------------------------------
| Gestores
|--------------------------------------------------------------------------
*/

$stmtGestores = $pdo->query("
    SELECT
        u.id,
        u.nombre,
        u.apellido_paterno,
        u.apellido_materno
    FROM usuarios u

    INNER JOIN roles r
        ON r.id = u.rol_id

    WHERE
        u.activo = 1
        AND r.activo = 1
        AND r.nombre = 'Gestor'

    ORDER BY
        u.nombre ASC,
        u.apellido_paterno ASC
");

$gestores = $stmtGestores->fetchAll();


/*
|--------------------------------------------------------------------------
| Usuario actual
|--------------------------------------------------------------------------
*/

$usuarioActual = trim(
    usuarioNombre()
);

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
        Nuevo expediente
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css">

    <link
        rel="stylesheet"
        href="../css/expedientes.css">

    <link
        rel="stylesheet"
        href="../css/crear-expediente.css">

</head>


<body>

    <div class="app">


        <!-- =========================
         SIDEBAR
    ========================== -->

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
                    class="logout-button">
                    🚪 Cerrar sesión
                </a>

            </div>

        </aside>


        <!-- =========================
         CONTENIDO
    ========================== -->

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
                        Nuevo expediente
                    </strong>

                    <span>
                        Captura progresiva
                    </span>

                </div>


                <div class="user-area">

                    <div class="user-avatar">

                        <?= htmlspecialchars(
                            mb_strtoupper(
                                mb_substr(
                                    $usuarioActual,
                                    0,
                                    1
                                )
                            ),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                    <div class="user-info">

                        <strong>

                            <?= htmlspecialchars(
                                $usuarioActual,
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


            <!-- =========================
             FORMULARIO
        ========================== -->

            <section class="content crear-expediente-content">


                <!-- ENCABEZADO -->

                <div class="create-heading">

                    <div>

                        <span>
                            EXPEDIENTES
                        </span>

                        <h1>
                            Crear expediente
                        </h1>

                        <p>
                            Captura únicamente la información
                            disponible en este momento.
                        </p>

                    </div>


                    <a
                        href="index.php"
                        class="btn-secondary">
                        ← Regresar
                    </a>

                </div>


                <form
                    action="guardar.php"
                    method="POST"
                    id="formCrearExpediente"
                    autocomplete="off">


                    <!-- =====================
                     PASO 1
                ====================== -->

                    <section class="form-card">

                        <div class="form-card-header">

                            <div class="step-number">
                                1
                            </div>

                            <div>

                                <h2>
                                    Datos del expediente
                                </h2>

                                <p>
                                    Información básica del trámite.
                                </p>

                            </div>

                        </div>


                        <div class="form-grid">


                            <!-- TIPO -->

                            <div class="form-group">

                                <label for="tipo_tramite_id">

                                    Tipo de trámite

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <select
                                    name="tipo_tramite_id"
                                    id="tipo_tramite_id"
                                    required>

                                    <option value="">
                                        Seleccionar trámite...
                                    </option>

                                    <?php foreach (
                                        $tiposTramite
                                        as $tipo
                                    ): ?>

                                        <option
                                            value="<?= (int) $tipo['id'] ?>">

                                            <?= htmlspecialchars(
                                                $tipo['nombre'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <small>
                                    Las etapas y documentos
                                    dependerán del tipo de trámite.
                                </small>

                            </div>


                            <!-- ESTADO -->

                            <div class="form-group">

                                <label for="estado_id">

                                    Estado

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <select
                                    name="estado_id"
                                    id="estado_id"
                                    required>

                                    <option value="">
                                        Seleccionar estado...
                                    </option>

                                    <?php foreach (
                                        $estados
                                        as $estado
                                    ): ?>

                                        <option
                                            value="<?= (int) $estado['id'] ?>">

                                            <?= htmlspecialchars(
                                                $estado['nombre'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- ETAPA -->

                            <div class="form-group">

                                <label for="etapa_actual_id">

                                    Etapa inicial

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <select
                                    name="etapa_actual_id"
                                    id="etapa_actual_id"
                                    required>

                                    <option value="">
                                        Seleccionar etapa...
                                    </option>

                                    <?php foreach (
                                        $etapas
                                        as $etapa
                                    ): ?>

                                        <option
                                            value="<?= (int) $etapa['id'] ?>">

                                            <?= htmlspecialchars(
                                                $etapa['nombre'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <small
                                    id="etapaAyuda">
                                    Selecciona primero el tipo
                                    de trámite.
                                </small>

                            </div>


                            <!-- GESTOR -->

                            <div class="form-group">

                                <label for="gestor_id">

                                    Gestor

                                </label>

                                <select
                                    name="gestor_id"
                                    id="gestor_id">

                                    <option value="">
                                        Sin asignar por ahora
                                    </option>

                                    <?php foreach (
                                        $gestores
                                        as $gestor
                                    ): ?>

                                        <?php

                                        $nombreGestor =
                                            trim(
                                                $gestor['nombre']
                                                    . ' '
                                                    . $gestor['apellido_paterno']
                                                    . ' '
                                                    . (
                                                        $gestor['apellido_materno'] ?? ''
                                                    )
                                            );

                                        ?>

                                        <option
                                            value="<?= (int) $gestor['id'] ?>">

                                            <?= htmlspecialchars(
                                                $nombreGestor,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <small>
                                    Puede asignarse después.
                                </small>

                            </div>


                            <!-- OBSERVACIONES -->

                            <div
                                class="form-group form-group-full">

                                <label for="observaciones">

                                    Observaciones iniciales

                                </label>

                                <textarea
                                    name="observaciones"
                                    id="observaciones"
                                    rows="4"
                                    maxlength="5000"
                                    placeholder="Anota información importante del expediente..."></textarea>

                            </div>

                        </div>

                    </section>


                    <!-- =====================
                     PASO 2
                ====================== -->

                    <section class="form-card">

                        <div class="form-card-header">

                            <div class="step-number">
                                2
                            </div>

                            <div>

                                <h2>
                                    Cliente
                                </h2>

                                <p>
                                    Selecciona un cliente existente o crea
                                    una nueva persona.
                                </p>

                            </div>

                        </div>


                        <div class="client-selector">

                            <!-- BUSCADOR -->

                            <div class="client-search">

                                <label for="buscarCliente">
                                    Buscar cliente
                                </label>

                                <div class="client-search-row">

                                    <input
                                        type="search"
                                        id="buscarCliente"
                                        placeholder="Nombre, apellido, CURP o RFC...">

                                    <button
                                        type="button"
                                        id="btnBuscarCliente"
                                        class="btn-secondary">
                                        🔎 Buscar
                                    </button>

                                </div>

                            </div>


                            <!-- CREAR PERSONA -->

                            <div class="new-client-box">

                                <div>

                                    <strong>
                                        ¿No encuentras al cliente?
                                    </strong>

                                    <span>
                                        Puedes registrarlo ahora y continuar
                                        con este expediente.
                                    </span>

                                </div>

                                <button
                                    type="button"
                                    id="btnNuevaPersona"
                                    class="btn-new-client">
                                    ➕ Crear persona
                                </button>

                            </div>


                            <!-- RESULTADOS -->

                            <div
                                id="resultadosClientes"
                                class="client-results">

                                <div class="client-placeholder">

                                    Busca una persona existente
                                    o crea una nueva.

                                </div>

                            </div>


                            <!-- CLIENTE SELECCIONADO -->

                            <input
                                type="hidden"
                                name="cliente_id"
                                id="cliente_id"
                                value="">


                            <div
                                id="clienteSeleccionado"
                                class="selected-client"
                                hidden>

                                <div>

                                    <span>
                                        Cliente seleccionado
                                    </span>

                                    <strong
                                        id="clienteNombre"></strong>

                                </div>

                                <button
                                    type="button"
                                    id="quitarCliente"
                                    class="remove-client">
                                    Cambiar
                                </button>

                            </div>

                        </div>

                    </section>


                    <!-- =====================
                     PASO 3
                ====================== -->

                    <section class="form-card future-section">

                        <div class="form-card-header">

                            <div class="step-number">
                                3
                            </div>

                            <div>

                                <h2>
                                    Participantes
                                </h2>

                                <p>
                                    Se agregarán después de crear
                                    el expediente.
                                </p>

                            </div>

                            <span class="future-badge">
                                Después
                            </span>

                        </div>


                        <div class="future-message">

                            <span>
                                👥
                            </span>

                            <div>

                                <strong>
                                    Participantes del acto jurídico
                                </strong>

                                <p>
                                    Aquí agregaremos vendedor,
                                    comprador, donante, donatario,
                                    testador, heredero, albacea,
                                    apoderado, representante, etc.
                                </p>

                            </div>

                        </div>

                    </section>


                    <!-- =====================
                     PASO 4
                ====================== -->

                    <section class="form-card future-section">

                        <div class="form-card-header">

                            <div class="step-number">
                                4
                            </div>

                            <div>

                                <h2>
                                    Datos del inmueble
                                </h2>

                                <p>
                                    Administración captura
                                    esta información.
                                </p>

                            </div>

                            <span class="admin-badge">
                                🔒 Administración
                            </span>

                        </div>


                        <div class="future-message">

                            <span>
                                🏠
                            </span>

                            <div>

                                <strong>
                                    Información administrada
                                    por Administración
                                </strong>

                                <p>
                                    Los gestores podrán consultar
                                    estos datos posteriormente,
                                    pero no modificarlos.
                                </p>

                            </div>

                        </div>

                    </section>


                    <!-- =====================
                     PASO 5
                ====================== -->

                    <section class="form-card future-section">

                        <div class="form-card-header">

                            <div class="step-number">
                                5
                            </div>

                            <div>

                                <h2>
                                    Documentación
                                </h2>

                                <p>
                                    Se configurará posteriormente
                                    según el tipo de trámite.
                                </p>

                            </div>

                            <span class="future-badge">
                                Después
                            </span>

                        </div>


                        <div class="future-message">

                            <span>
                                📎
                            </span>

                            <div>

                                <strong>
                                    Documentos requeridos
                                </strong>

                                <p>
                                    El sistema permitirá incorporar
                                    documentos progresivamente y
                                    posteriormente podremos ampliar
                                    la lista de documentos necesarios.
                                </p>

                            </div>

                        </div>

                    </section>


                    <!-- =====================
                     BOTONES
                ====================== -->

                    <div class="form-actions">

                        <a
                            href="index.php"
                            class="btn-secondary">
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn-primary">
                            💾 Crear expediente
                        </button>

                    </div>


                </form>


            </section>

        </main>

    </div>


    <!-- ACCIONES RÁPIDAS -->

    <nav
        class="mobile-actions"
        aria-label="Acciones rápidas">

        <a
            href="crear.php"
            class="mobile-action">

            <span class="mobile-action-icon">
                ➕
            </span>

            <span>
                Nuevo
            </span>

        </a>


        <a
            href="index.php#buscar"
            class="mobile-action">

            <span class="mobile-action-icon">
                🔎
            </span>

            <span>
                Buscar
            </span>

        </a>


        <a
            href="#"
            class="mobile-action">

            <span class="mobile-action-icon">
                📎
            </span>

            <span>
                Subir
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


    <script src="../js/dashboard.js"></script>

    <script src="../js/crear-expediente.js"></script>


    <div
        class="modal-overlay"
        id="modalNuevaPersona"
        hidden>

        <div
            class="persona-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tituloNuevaPersona">

            <div class="persona-modal-header">

                <div>

                    <span>
                        NUEVA PERSONA
                    </span>

                    <h2 id="tituloNuevaPersona">
                        Registrar persona
                    </h2>

                    <p>
                        Los datos marcados con * son obligatorios.
                    </p>

                </div>

                <button
                    type="button"
                    class="modal-close"
                    id="cerrarModalPersona"
                    aria-label="Cerrar">
                    ✕
                </button>

            </div>


            <form
                id="formNuevaPersona">

                <div class="persona-form-grid">


                    <!-- NOMBRE -->

                    <div class="form-group">

                        <label for="persona_nombre">

                            Nombre

                            <span class="required">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            name="nombre"
                            id="persona_nombre"
                            maxlength="100"
                            required>

                    </div>


                    <!-- APELLIDO PATERNO -->

                    <div class="form-group">

                        <label for="persona_apellido_paterno">

                            Apellido paterno

                            <span class="required">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            name="apellido_paterno"
                            id="persona_apellido_paterno"
                            maxlength="100"
                            required>

                    </div>


                    <!-- APELLIDO MATERNO -->

                    <div class="form-group">

                        <label for="persona_apellido_materno">

                            Apellido materno

                        </label>

                        <input
                            type="text"
                            name="apellido_materno"
                            id="persona_apellido_materno"
                            maxlength="100">

                    </div>


                    <!-- FECHA NACIMIENTO -->

                    <div class="form-group">

                        <label for="persona_fecha_nacimiento">

                            Fecha de nacimiento

                        </label>

                        <input
                            type="date"
                            name="fecha_nacimiento"
                            id="persona_fecha_nacimiento">

                    </div>


                    <!-- ESTADO CIVIL -->

                    <div class="form-group">

                        <label for="persona_estado_civil_id">

                            Estado civil

                        </label>

                        <select
                            name="estado_civil_id"
                            id="persona_estado_civil_id">

                            <option value="">
                                Seleccionar...
                            </option>

                            <?php

                            $stmtEstadosCiviles =
                                $pdo->query("
                                SELECT
                                    id,
                                    nombre
                                FROM estados_civiles
                                ORDER BY nombre ASC
                            ");

                            $estadosCiviles =
                                $stmtEstadosCiviles->fetchAll();

                            ?>

                            <?php foreach (
                                $estadosCiviles
                                as $estadoCivil
                            ): ?>

                                <option
                                    value="<?= (int) $estadoCivil['id'] ?>">

                                    <?= htmlspecialchars(
                                        $estadoCivil['nombre'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- CURP -->

                    <div class="form-group">

                        <label for="persona_curp">
                            CURP
                        </label>

                        <input
                            type="text"
                            name="curp"
                            id="persona_curp"
                            maxlength="18"
                            style="text-transform: uppercase;">

                    </div>


                    <!-- RFC -->

                    <div class="form-group">

                        <label for="persona_rfc">
                            RFC
                        </label>

                        <input
                            type="text"
                            name="rfc"
                            id="persona_rfc"
                            maxlength="13"
                            style="text-transform: uppercase;">

                    </div>


                    <!-- TELÉFONO -->

                    <div class="form-group">

                        <label for="persona_telefono">
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            name="telefono"
                            id="persona_telefono"
                            maxlength="20">

                    </div>


                    <!-- CORREO -->

                    <div class="form-group">

                        <label for="persona_correo">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            name="correo"
                            id="persona_correo"
                            maxlength="150">

                    </div>


                    <!-- DOMICILIO -->

                    <div
                        class="form-group form-group-full">

                        <label for="persona_domicilio">
                            Domicilio
                        </label>

                        <textarea
                            name="domicilio"
                            id="persona_domicilio"
                            rows="3"></textarea>

                    </div>


                    <!-- OBSERVACIONES -->

                    <div
                        class="form-group form-group-full">

                        <label for="persona_observaciones">
                            Observaciones
                        </label>

                        <textarea
                            name="observaciones"
                            id="persona_observaciones"
                            rows="3"></textarea>

                    </div>

                </div>


                <div
                    id="personaError"
                    class="persona-error"
                    hidden></div>


                <div class="persona-modal-actions">

                    <button
                        type="button"
                        class="btn-secondary"
                        id="cancelarNuevaPersona">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                        id="guardarNuevaPersona">
                        💾 Guardar persona
                    </button>

                </div>

            </form>

        </div>

    </div>
</body>

</html>
<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/auth.php';

exigirAutenticacion();

/*
|--------------------------------------------------------------------------
| Información del usuario
|--------------------------------------------------------------------------
*/

$nombreUsuario = usuarioNombre();
$rolNombre = $_SESSION['rol_nombre'] ?? '';

/*
|--------------------------------------------------------------------------
| Contadores
|--------------------------------------------------------------------------
*/

$totalExpedientes = 0;
$totalDocumentos = 0;
$totalObservaciones = 0;
$totalNotificaciones = 0;

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM expedientes
        WHERE activo = 1
        AND eliminado = 0
    ");

    $totalExpedientes = (int) $stmt->fetchColumn();
} catch (Throwable $e) {

    error_log($e->getMessage());
}

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM documentos
    ");

    $totalDocumentos = (int) $stmt->fetchColumn();
} catch (Throwable $e) {

    error_log($e->getMessage());
}

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM observaciones
        WHERE estado IN ('ABIERTA', 'EN_PROCESO')
    ");

    $totalObservaciones = (int) $stmt->fetchColumn();
} catch (Throwable $e) {

    error_log($e->getMessage());
}

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM notificaciones
        WHERE usuario_id = :usuario_id
        AND estado IN ('PENDIENTE', 'ERROR')
    ");

    $stmt->execute([
        ':usuario_id' => usuarioId()
    ]);

    $totalNotificaciones = (int) $stmt->fetchColumn();
} catch (Throwable $e) {

    error_log($e->getMessage());
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
        Dashboard | <?= htmlspecialchars(APP_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="css/dashboard.css">

</head>

<body>

    <div class="app">

        <!-- =========================
         MENÚ LATERAL
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
                    href="index.php"
                    class="menu-item active">
                    <span>🏠</span>
                    <span>Dashboard</span>
                </a>

                <a
                    href="../public/expedientes/index.php"
                    class="menu-item">
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

                <?php if (rolId() === 1): ?>

                    <div class="menu-section">
                        Administración
                    </div>

                    <a
                        href="#"
                        class="menu-item">
                        <span>👤</span>
                        <span>Usuarios</span>
                    </a>

                    <a
                        href="#"
                        class="menu-item">
                        <span>⚙️</span>
                        <span>Configuración</span>
                    </a>

                    <a
                        href="#"
                        class="menu-item">
                        <span>📜</span>
                        <span>Historial</span>
                    </a>

                <?php endif; ?>

            </nav>


            <div class="sidebar-footer">

                <a
                    href="logout.php"
                    class="logout-button">
                    🚪 Cerrar sesión
                </a>

            </div>

        </aside>


        <!-- =========================
         CONTENIDO
    ========================== -->

        <main class="main">

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
                        Dashboard
                    </strong>

                    <span>
                        Sistema de Escrituras
                    </span>

                </div>


                <div class="user-area">

                    <div class="user-avatar">
                        <?= strtoupper(
                            mb_substr(
                                $nombreUsuario,
                                0,
                                1
                            )
                        ) ?>
                    </div>

                    <div class="user-info">

                        <strong>
                            <?= htmlspecialchars(
                                $nombreUsuario,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                        <small>
                            <?= htmlspecialchars(
                                $rolNombre,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </small>

                    </div>

                </div>

            </header>


            <section class="content">

                <!-- =====================
                 BIENVENIDA
            ====================== -->

                <div class="welcome">

                    <div>

                        <span class="welcome-label">
                            PANEL PRINCIPAL
                        </span>

                        <h1>
                            Bienvenido,
                            <?= htmlspecialchars(
                                $nombreUsuario,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h1>

                        <p>
                            Aquí tienes un resumen
                            de la actividad del sistema.
                        </p>

                    </div>

                    <div class="welcome-icon">
                        📁
                    </div>

                </div>


                <!-- =====================
                 ESTADÍSTICAS
            ====================== -->

                <div class="stats-grid">

                    <div class="stat-card">

                        <div class="stat-icon">
                            📁
                        </div>

                        <div>

                            <span>
                                Expedientes activos
                            </span>

                            <strong>
                                <?= number_format(
                                    $totalExpedientes
                                ) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-icon">
                            📎
                        </div>

                        <div>

                            <span>
                                Documentos
                            </span>

                            <strong>
                                <?= number_format(
                                    $totalDocumentos
                                ) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-icon">
                            📝
                        </div>

                        <div>

                            <span>
                                Observaciones abiertas
                            </span>

                            <strong>
                                <?= number_format(
                                    $totalObservaciones
                                ) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-icon">
                            🔔
                        </div>

                        <div>

                            <span>
                                Notificaciones pendientes
                            </span>

                            <strong>
                                <?= number_format(
                                    $totalNotificaciones
                                ) ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- =====================
                 ACCIONES RÁPIDAS
            ====================== -->

                <div class="section-title">

                    <div>

                        <span>
                            ACCESO RÁPIDO
                        </span>

                        <h2>
                            Acciones frecuentes
                        </h2>

                    </div>

                </div>


                <div class="quick-grid">

                    <a
                        href="#"
                        class="quick-card">

                        <span class="quick-icon">
                            ➕
                        </span>

                        <strong>
                            Nuevo expediente
                        </strong>

                        <small>
                            Crear un nuevo expediente
                        </small>

                    </a>


                    <a
                        href="#"
                        class="quick-card">

                        <span class="quick-icon">
                            📎
                        </span>

                        <strong>
                            Documentos
                        </strong>

                        <small>
                            Consultar documentación
                        </small>

                    </a>


                    <a
                        href="#"
                        class="quick-card">

                        <span class="quick-icon">
                            🔎
                        </span>

                        <strong>
                            Revisiones
                        </strong>

                        <small>
                            Revisar documentación
                        </small>

                    </a>


                    <a
                        href="#"
                        class="quick-card">

                        <span class="quick-icon">
                            🔔
                        </span>

                        <strong>
                            Notificaciones
                        </strong>

                        <small>
                            Ver avisos pendientes
                        </small>

                    </a>

                </div>


                <!-- =====================
                 ESTADO DEL SISTEMA
            ====================== -->

                <div class="section-title">

                    <div>

                        <span>
                            INFORMACIÓN
                        </span>

                        <h2>
                            Estado del sistema
                        </h2>

                    </div>

                </div>


                <div class="system-card">

                    <div class="system-row">

                        <div>

                            <strong>
                                Base de datos
                            </strong>

                            <span>
                                Conexión activa
                            </span>

                        </div>

                        <span class="status success">
                            ● Operativa
                        </span>

                    </div>


                    <div class="system-row">

                        <div>

                            <strong>
                                Sesión
                            </strong>

                            <span>
                                Usuario autenticado
                            </span>

                        </div>

                        <span class="status success">
                            ● Activa
                        </span>

                    </div>


                    <div class="system-row">

                        <div>

                            <strong>
                                Rol
                            </strong>

                            <span>
                                <?= htmlspecialchars(
                                    $rolNombre,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>

                        </div>

                        <span class="status success">
                            ● Autorizado
                        </span>

                    </div>

                </div>

            </section>

        </main>

    </div>
    <!-- =========================
     ACCIONES RÁPIDAS MÓVIL
========================== -->

    <nav class="mobile-actions" aria-label="Acciones rápidas">

        <a href="#" class="mobile-action">
            <span class="mobile-action-icon">➕</span>
            <span>Nuevo</span>
        </a>

        <a href="#" class="mobile-action">
            <span class="mobile-action-icon">🔎</span>
            <span>Buscar</span>
        </a>

        <a href="#" class="mobile-action">
            <span class="mobile-action-icon">📎</span>
            <span>Subir</span>
        </a>

        <a href="#" class="mobile-action">
            <span class="mobile-action-icon">🔔</span>
            <span>Avisos</span>
        </a>

    </nav>


    <div
        class="sidebar-overlay"
        id="sidebarOverlay"></div>

    <script src="js/dashboard.js"></script>

</body>

</html>
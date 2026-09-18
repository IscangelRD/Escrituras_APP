<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Helpers/csrf.php';

if (usuarioAutenticado()) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? null;

unset($_SESSION['login_error']);

$csrf = csrfToken();

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
        <?= htmlspecialchars(APP_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="css/login.css"
    >

</head>

<body>

<div class="login-page">

    <div class="login-card">

        <div class="logo-area">

            <div class="logo-placeholder">
                IE
            </div>

            <h1>
                Sistema de Escrituras
            </h1>

            <p>
                Control y gestión de expedientes
            </p>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-error">

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <form
            action="login-procesar.php"
            method="POST"
            autocomplete="on"
            novalidate
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $csrf,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <div class="form-group">

                <label for="identificador">
                    Usuario o correo
                </label>

                <input
                    type="text"
                    id="identificador"
                    name="identificador"
                    autocomplete="username"
                    required
                    maxlength="150"
                    placeholder="Ingresa tu usuario o correo"
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Contraseña
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        placeholder="Ingresa tu contraseña"
                    >

                    <button
                        type="button"
                        id="togglePassword"
                        class="password-toggle"
                        aria-label="Mostrar contraseña"
                    >
                        👁
                    </button>

                </div>

            </div>

            <button
                type="submit"
                class="btn-login"
            >
                Iniciar sesión
            </button>

        </form>

        <div class="forgot-password">

            <a href="#">
                ¿Olvidaste tu contraseña?
            </a>

        </div>

        <div class="login-footer">

            <strong>
                <?= htmlspecialchars(
                    APP_NAME,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

            <span>
                Versión 1.0
            </span>

        </div>

    </div>

</div>

<script src="js/login.js"></script>

</body>

</html>
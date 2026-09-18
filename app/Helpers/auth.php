<?php

declare(strict_types=1);

function usuarioAutenticado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function usuarioId(): ?int
{
    return isset($_SESSION['usuario_id'])
        ? (int) $_SESSION['usuario_id']
        : null;
}

function rolId(): ?int
{
    return isset($_SESSION['rol_id'])
        ? (int) $_SESSION['rol_id']
        : null;
}

function usuarioNombre(): string
{
    return $_SESSION['usuario_nombre'] ?? '';
}

function exigirAutenticacion(): void
{
    if (!usuarioAutenticado()) {

        header('Location: login.php');

        exit;
    }
}
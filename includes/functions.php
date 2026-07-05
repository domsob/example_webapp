<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $token = $_POST['csrf_token'] ?? '';
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function is_admin_logged_in(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return !empty($_SESSION['admin_logged_in']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: admin.php');
        exit;
    }
}

function format_date(string $datetime): string
{
    return date('F j, Y', strtotime($datetime));
}

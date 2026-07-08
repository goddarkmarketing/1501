<?php
require_once __DIR__ . '/db.php';

function attempt(string $username, string $password): bool {
    $user = fetchOne('SELECT * FROM admin_users WHERE username = ?', [$username]);
    if (!$user) return false;
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id']   = $user['id'];
        $_SESSION['admin_name'] = $user['display_name'];
        return true;
    }
    return false;
}

function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function adminName(): string {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function logout(): void {
    session_destroy();
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

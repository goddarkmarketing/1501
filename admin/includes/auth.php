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

function requireAdminAccess(): void {
    if (isLoggedIn()) {
        return;
    }
    require_once __DIR__ . '/feedback-lib.php';
    if (feedbackPreviewRequested()) {
        feedbackActivatePreview();
    }
    if (feedbackPreviewActive() && isFeedbackReview()) {
        return;
    }
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

function adminName(): string {
    if (empty($_SESSION['admin_id']) && !empty($_SESSION['feedback_review'])) {
        return 'ผู้ตรวจงาน';
    }
    return $_SESSION['admin_name'] ?? 'Admin';
}

function logout(): void {
    session_destroy();
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

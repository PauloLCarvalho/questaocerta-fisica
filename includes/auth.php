<?php
// Centralized auth helpers
// Usage: include_once __DIR__ . '/includes/auth.php' (from project root use '../includes/auth.php')
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function qc_is_logged() {
    return isset($_SESSION['email']);
}

function qc_is_admin() {
    return isset($_SESSION['nivel']) && $_SESSION['nivel'] === 'admin';
}

function qc_require_login() {
    if (!qc_is_logged()) {
        header('Location: ../views/index.html');
        exit;
    }
}

function qc_require_admin() {
    qc_require_login();
    if (!qc_is_admin()) {
        header('Location: ../views/dashboard.html');
        exit;
    }
}

// helper to get sanitized session name
function qc_user_name() {
    return isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : '';
}

?>

<?php
// Centralized auth helpers and security utilities
// Ensure secure session cookie parameters BEFORE session_start.
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        // fallback for older PHP (not expected here) - use legacy signature
        session_set_cookie_params(0, '/; samesite=Strict', '', $secure, true);
    }
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
        header('Location: ../views/dashboard.php');
        exit;
    }
}

// helper to get sanitized session name
function qc_user_name() {
    return isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : '';
}

function qc_user_email() {
    return isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '';
}

// CSRF utilities -----------------------------------------------------------
function qc_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function qc_csrf_validate($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    // optional expiry: 2h
    if (!empty($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 7200) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    if ($valid) { 
        // Regenerate token after successful validation for next request
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $valid;
}

function qc_require_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!qc_csrf_validate($token)) {
        http_response_code(403);
        echo 'CSRF token inválido ou expirado.';
        exit;
    }
}

?>

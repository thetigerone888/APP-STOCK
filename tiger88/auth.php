<?php
// ========================================================
// Tiger88 Expense System — Authentication helpers
// ========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

function require_login() {
    if (empty($_SESSION['logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

function require_login_api() {
    if (empty($_SESSION['logged_in'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'ยังไม่ได้เข้าสู่ระบบ']);
        exit;
    }
}

function attempt_login($password) {
    if (password_verify($password, LOGIN_PASSWORD_HASH)) {
        $_SESSION['logged_in'] = true;
        session_regenerate_id(true);
        return true;
    }
    return false;
}

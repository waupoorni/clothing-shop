<?php
// Session + CSRF helpers
if (session_status() === PHP_SESSION_NONE) session_start();

function is_admin() {
  return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_admin() {
  if (!is_admin()) {
    header("Location: login.php");
    exit;
  }
}

function csrf_token() {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? "");
}

<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy all session data
session_unset();
session_destroy();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page using BASE_URL
header('Location: ' . BASE_URL . 'login.php');
exit();

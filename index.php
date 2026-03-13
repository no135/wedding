<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Auth.php';

$auth = new Auth();

// If logged in, redirect to admin
if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/admin/dashboard.php');
    exit;
}

// Redirect to login
header('Location: ' . APP_URL . '/login.php');
exit;

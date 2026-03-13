<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: ' . APP_URL . '/login.php?message=logged-out');
exit;

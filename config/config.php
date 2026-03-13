<?php
/**
 * Wedding Invitation System - Configuration File
 */

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'wedding');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Application Settings
define('APP_NAME', 'Wedding Invitation System');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'webm']);

// Timezone
date_default_timezone_set('UTC');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS Headers
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Security Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

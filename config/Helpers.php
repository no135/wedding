<?php
/**
 * Helper Functions
 */

/**
 * Sanitize output to prevent XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Generate UUID
 */
function generateUUID() {
    return bin2hex(random_bytes(16));
}

/**
 * Generate slug
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Get time ago
 */
function getTimeAgo($datetime) {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $interval = $now->diff($then);

    if ($interval->y > 0) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    } elseif ($interval->m > 0) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    } elseif ($interval->d > 0) {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    } elseif ($interval->h > 0) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    } elseif ($interval->i > 0) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'just now';
    }
}

/**
 * Upload file
 */
function uploadFile($file, $upload_dir, $allowed_types) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file provided'];
    }

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }

    $file_name = generateUUID() . '.' . $file_ext;
    $upload_path = $upload_dir . $file_name;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'file_name' => $file_name, 'file_path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $upload_path)];
    }

    return ['success' => false, 'message' => 'File upload failed'];
}

/**
 * Send response
 */
function sendResponse($success = true, $message = '', $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Get client IP
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'UNKNOWN';
}

/**
 * Create QR code token
 */
function generateQRToken() {
    return strtoupper(bin2hex(random_bytes(8)));
}

/**
 * Truncate string
 */
function truncate($string, $length = 100, $suffix = '...') {
    if (strlen($string) > $length) {
        return substr($string, 0, $length - strlen($suffix)) . $suffix;
    }
    return $string;
}

<?php
/**
 * Middleware Class for Route Protection and Authorization
 */

class Middleware {
    private $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    /**
     * Require login
     */
    public function requireLogin() {
        if (!$this->auth->isLoggedIn()) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }

        if (!$this->auth->checkSessionTimeout()) {
            header('Location: ' . APP_URL . '/login.php?message=Session%20expired');
            exit;
        }
    }

    /**
     * Require admin role
     */
    public function requireAdmin() {
        $this->requireLogin();
        
        if (!$this->auth->isAdmin()) {
            http_response_code(403);
            die('Access Denied: Admin privileges required');
        }
    }

    /**
     * Require user role (default)
     */
    public function requireUser() {
        $this->requireLogin();
        
        $user = $this->auth->getCurrentUser();
        if ($user['role'] !== 'user') {
            http_response_code(403);
            die('Access Denied: User privileges required');
        }
    }

    /**
     * CSRF Protection
     */
    public function verifyCsrf($token) {
        if (!Auth::verifyToken($token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token verification failed']));
        }
    }

    /**
     * Validate request method
     */
    public function validateMethod($allowed_methods = ['GET']) {
        $method = $_SERVER['REQUEST_METHOD'];
        if (!in_array($method, $allowed_methods)) {
            http_response_code(405);
            die(json_encode(['error' => 'Method not allowed']));
        }
    }

    /**
     * Rate limiting (basic)
     */
    public function rateLimit($identifier, $max_requests = 100, $window = 3600) {
        $cache_key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$cache_key])) {
            $_SESSION[$cache_key] = ['count' => 1, 'window' => time()];
            return true;
        }

        $data = $_SESSION[$cache_key];
        
        if (time() - $data['window'] > $window) {
            $_SESSION[$cache_key] = ['count' => 1, 'window' => time()];
            return true;
        }

        if ($data['count'] >= $max_requests) {
            http_response_code(429);
            die(json_encode(['error' => 'Too many requests']));
        }

        $_SESSION[$cache_key]['count']++;
        return true;
    }
}

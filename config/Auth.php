<?php
/**
 * Authentication Helper Class
 */

class Auth {
    private $db;

    public function __construct() {
        $db = new Database();
        $this->db = $db->getConnection();
    }

    /**
     * Register new user
     */
    public function register($fullname, $email, $password) {
        try {
            // Check if user exists
            $query = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user
            $query = "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$fullname, $email, $hashed_password, 'user']);

            if ($result) {
                return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $this->db->lastInsertId()];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
        }
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            $query = "SELECT id, fullname, email, password, role FROM users WHERE email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'fullname' => $_SESSION['fullname'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }

    /**
     * Check session timeout
     */
    public function checkSessionTimeout() {
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_TIMEOUT)) {
            $this->logout();
            return false;
        }
        $_SESSION['login_time'] = time();
        return true;
    }

    /**
     * Verify CSRF token
     */
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

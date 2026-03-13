<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Auth.php';
require_once __DIR__ . '/config/Helpers.php';

$auth = new Auth();
$error = '';
$success = '';

// If already logged in, redirect
if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/admin/dashboard.php');
    exit;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } else {
        $result = $auth->register($fullname, $email, $password);
        if ($result['success']) {
            $success = 'Registration successful! Redirecting to login...';
            header('refresh:2;url=' . APP_URL . '/login.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
        }

        .register-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .register-header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: 700;
        }

        .register-header p {
            font-size: 14px;
            margin: 5px 0 0 0;
            opacity: 0.9;
        }

        .register-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-register:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .form-text {
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card register-card">
            <div class="register-header">
                <h1><i class="fas fa-heart"></i> <?php echo APP_NAME; ?></h1>
                <p>Create Your Account</p>
            </div>
            <div class="register-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Your Name" required>
                        <small class="form-text">Your full name as it will appear in the system</small>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>
                        <small class="form-text">We'll use this email to contact you</small>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimum 6 characters" required>
                        <small class="form-text">Must be at least 6 characters long</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Confirm your password" required>
                    </div>

                    <button type="submit" class="btn btn-register w-100">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="<?php echo APP_URL; ?>/login.php">Sign in here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

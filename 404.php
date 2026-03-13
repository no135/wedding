<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - <?php echo APP_NAME; ?></title>
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
            color: white;
        }

        .error-container {
            text-align: center;
            padding: 20px;
        }

        .error-code {
            font-size: 120px;
            font-weight: 700;
            margin: 0;
            opacity: 0.9;
        }

        .error-title {
            font-size: 32px;
            margin: 20px 0;
        }

        .error-message {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .btn-home {
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-home:hover {
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">Sorry, the page you're looking for doesn't exist or has been moved.</p>
        <a href="<?php echo APP_URL; ?>/login.php" class="btn-home">
            <i class="fas fa-home"></i> Go to Login
        </a>
    </div>
</body>
</html>

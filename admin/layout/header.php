<?php
/**
 * Admin Header Layout
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6366f1">
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' - ' : ''; ?><?php echo APP_NAME; ?> Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/admin/assets/css/admin.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #ec4899;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --light-bg: #f9fafb;
            --dark-text: #1f2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        .sidebar {
            background: white;
            border-right: 1px solid #e5e7eb;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            z-index: 1000;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .sidebar-logo {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
        }

        .sidebar-menu li {
            padding: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background-color: var(--light-bg);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .sidebar-menu a.active {
            background-color: #eef2ff;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }

        .sidebar-title {
            padding: 15px 20px 10px;
            font-size: 12px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        .badge {
            border-radius: 6px;
            font-weight: 500;
            padding: 6px 12px;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-card .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }

        .stat-card .stat-icon {
            float: right;
            font-size: 32px;
            color: #e5e7eb;
        }

        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--light-bg);
        }

        .table th {
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
        }

        .table td {
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .form-control, .form-select {
            border-color: #e5e7eb;
            border-radius: 6px;
            padding: 10px 12px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .alert {
            border-radius: 6px;
            border: none;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding: 10px;
                gap: 5px;
            }

            .sidebar-menu li {
                flex-shrink: 0;
            }

            .sidebar-menu a {
                padding: 10px 15px;
                white-space: nowrap;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-heart"></i> <?php echo APP_NAME; ?>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu</li>
            <li><a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="<?php echo APP_URL; ?>/admin/invitations.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'invitations.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Invitations</a></li>
            <li><a href="<?php echo APP_URL; ?>/admin/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="<?php echo APP_URL; ?>/admin/payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="<?php echo APP_URL; ?>/admin/guests.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'guests.php' ? 'active' : ''; ?>"><i class="fas fa-people-group"></i> Guests & RSVPs</a></li>
            <li><a href="<?php echo APP_URL; ?>/admin/packages.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'packages.php' ? 'active' : ''; ?>"><i class="fas fa-box"></i> Packages</a></li>
            <li><a href="<?php echo APP_URL; ?>/admin/themes.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'themes.php' ? 'active' : ''; ?>"><i class="fas fa-palette"></i> Themes</a></li>
            
            <li class="sidebar-title">Other</li>
            <li><a href="<?php echo APP_URL; ?>/admin/settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="<?php echo APP_URL; ?>/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <h4><?php echo isset($page_title) ? sanitize($page_title) : 'Dashboard'; ?></h4>
            </div>
            <div class="user-menu">
                <span><?php echo isset($_SESSION['fullname']) ? sanitize($_SESSION['fullname']) : 'Admin'; ?></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr(isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'A', 0, 1)); ?>
                </div>
            </div>
        </div>

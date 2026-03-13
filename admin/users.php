<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/User.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Manage Users';
$user = new User();

// Get all users
$users = $user->getAllUsers();

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Actions -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5>Total Users: <span class="text-primary"><?php echo count($users); ?></span></h5>
            </div>
            <a href="<?php echo APP_URL; ?>/admin/user-create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New User
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Invitations</th>
                                <th>Payments</th>
                                <th>Total Paid</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong>#<?php echo $u['id']; ?></strong></td>
                                    <td><?php echo sanitize($u['fullname']); ?></td>
                                    <td><small><?php echo sanitize($u['email']); ?></small></td>
                                    <td>
                                        <span class="badge <?php echo $u['role'] === 'admin' ? 'bg-danger' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($u['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $u['total_invitations'] ?? 0; ?></td>
                                    <td><?php echo $u['total_payments'] ?? 0; ?></td>
                                    <td><strong>$<?php echo number_format($u['total_paid'] ?? 0, 2); ?></strong></td>
                                    <td><small><?php echo formatDate($u['created_at'], 'M d, Y'); ?></small></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo APP_URL; ?>/admin/user-detail.php?id=<?php echo $u['id']; ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/admin/user-edit.php?id=<?php echo $u['id']; ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                <a href="<?php echo APP_URL; ?>/admin/user-delete.php?id=<?php echo $u['id']; ?>" class="btn btn-outline-danger" title="Delete" data-action="delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No users found
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

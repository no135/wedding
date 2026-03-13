<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Invitation.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/RSVP.php';

// Check admin access
$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Dashboard';

// Get statistics
$invitation = new Invitation();
$user = new User();
$payment = new Payment();
$rsvp = new RSVP();

$total_invitations = $invitation->getTotalInvitations();
$active_invitations = $invitation->getInvitationsByStatus('active');
$pending_invitations = $invitation->getInvitationsByStatus('pending_approval');
$total_users = $user->getTotalUsers();
$revenue_stats = $payment->getTotalRevenue();
$pending_payments = $payment->getPendingPayments();

// Recent invitations
$db = new Database();
$conn = $db->getConnection();
$query = "
    SELECT i.*, u.fullname, p.package_name
    FROM invitations i
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN packages p ON i.package_id = p.id
    ORDER BY i.created_at DESC
    LIMIT 10
";
$recent_invitations = $db->fetchAll($query);

// Recent payments
$query = "
    SELECT p.*, u.fullname, i.host_name_1
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN invitations i ON p.invitation_id = i.id
    ORDER BY p.created_at DESC
    LIMIT 10
";
$recent_payments = $db->fetchAll($query);

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="stat-value"><?php echo $total_invitations; ?></div>
                <div class="stat-label">Total Invitations</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value"><?php echo $active_invitations; ?></div>
                <div class="stat-label">Active Invitations</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-credit-card"></i></div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['approved'] ?? 0, 2); ?></div>
                <div class="stat-label">Approved Revenue</div>
            </div>
        </div>
    </div>

    <!-- Second Row Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-value"><?php echo $pending_payments; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                <div class="stat-value"><?php echo $pending_invitations; ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['pending'] ?? 0, 2); ?></div>
                <div class="stat-label">Pending Revenue</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['rejected'] ?? 0, 2); ?></div>
                <div class="stat-label">Rejected Revenue</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Recent Invitations -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Recent Invitations</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_invitations)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Host</th>
                                        <th>Package</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_invitations as $inv): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo sanitize($inv['host_name_1']); ?></strong><br>
                                                <small class="text-muted"><?php echo sanitize($inv['fullname']); ?></small>
                                            </td>
                                            <td><?php echo sanitize($inv['package_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $status_classes = [
                                                    'pending_payment' => 'warning',
                                                    'pending_approval' => 'info',
                                                    'active' => 'success',
                                                    'expired' => 'danger'
                                                ];
                                                $badge_class = $status_classes[$inv['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $badge_class; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $inv['status'])); ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo formatDate($inv['created_at'], 'M d, Y'); ?></small></td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/admin/invitation-detail.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No invitations yet</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <a href="<?php echo APP_URL; ?>/admin/invitations.php" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Recent Payments</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_payments)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_payments as $pmt): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo sanitize($pmt['fullname'] ?? 'N/A'); ?></strong><br>
                                                <small class="text-muted"><?php echo sanitize($pmt['host_name_1'] ?? 'General'); ?></small>
                                            </td>
                                            <td><strong>$<?php echo number_format($pmt['amount'], 2); ?></strong></td>
                                            <td>
                                                <?php
                                                $status_badge = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                $badge = $status_badge[$pmt['payment_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $badge; ?>">
                                                    <?php echo ucfirst($pmt['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo formatDate($pmt['created_at'], 'M d, Y'); ?></small></td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/admin/payment-detail.php?id=<?php echo $pmt['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No payments yet</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <a href="<?php echo APP_URL; ?>/admin/payments.php" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Application:</strong> <?php echo APP_NAME; ?></p>
                            <p><strong>Environment:</strong> <?php echo APP_ENV; ?></p>
                            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Database:</strong> <?php echo DB_NAME; ?> @ <?php echo DB_HOST; ?></p>
                            <p><strong>Current User:</strong> <?php echo sanitize($_SESSION['fullname']); ?> (<?php echo sanitize($_SESSION['email']); ?>)</p>
                            <p><strong>Last Login:</strong> <?php echo formatDate(date('Y-m-d H:i:s', $_SESSION['login_time']), 'M d, Y H:i A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

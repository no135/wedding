<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Payment.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Manage Payments';
$payment = new Payment();

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;

// Get payments
$db = new Database();
if ($status_filter && $status_filter !== 'all') {
    $payments = $payment->getPaymentsByStatus($status_filter);
} else {
    $query = "
        SELECT 
            p.*,
            u.fullname,
            u.email,
            i.host_name_1,
            pkg.package_name
        FROM payments p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN invitations i ON p.invitation_id = i.id
        LEFT JOIN packages pkg ON i.package_id = pkg.id
        ORDER BY p.created_at DESC
    ";
    $payments = $db->fetchAll($query);
}

$revenue_stats = $payment->getTotalRevenue();

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Revenue Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle text-success"></i></div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['approved'] ?? 0, 2); ?></div>
                <div class="stat-label">Approved Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock text-warning"></i></div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['pending'] ?? 0, 2); ?></div>
                <div class="stat-label">Pending Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-times-circle text-danger"></i></div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['rejected'] ?? 0, 2); ?></div>
                <div class="stat-label">Rejected Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-value"><?php echo $revenue_stats['total_transactions'] ?? 0; ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Invitation</th>
                                <th>Amount</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pmt): ?>
                                <tr>
                                    <td><strong>#<?php echo $pmt['id']; ?></strong></td>
                                    <td><?php echo sanitize($pmt['fullname'] ?? 'N/A'); ?></td>
                                    <td><small><?php echo sanitize($pmt['email'] ?? 'N/A'); ?></small></td>
                                    <td><small><?php echo sanitize($pmt['host_name_1'] ?? 'General'); ?></small></td>
                                    <td><strong>$<?php echo number_format($pmt['amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($pmt['payment_action']); ?>
                                        </span>
                                    </td>
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
                                    <td><small><?php echo formatDate($pmt['created_at'], 'M d, Y H:i'); ?></small></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo APP_URL; ?>/admin/payment-detail.php?id=<?php echo $pmt['id']; ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($pmt['payment_status'] === 'pending'): ?>
                                                <a href="<?php echo APP_URL; ?>/admin/payment-approve.php?id=<?php echo $pmt['id']; ?>" class="btn btn-outline-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/admin/payment-reject.php?id=<?php echo $pmt['id']; ?>" class="btn btn-outline-danger" title="Reject">
                                                    <i class="fas fa-times"></i>
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
                    <i class="fas fa-info-circle"></i> No payments found
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

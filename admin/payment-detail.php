<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Payment.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Payment Details';
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($payment_id)) {
    header('Location: ' . APP_URL . '/admin/payments.php');
    exit;
}

$payment = new Payment();
$pmt = $payment->getPaymentWithDetails($payment_id);

if (!$pmt) {
    header('Location: ' . APP_URL . '/404.php');
    exit;
}

// Handle approval/rejection
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';

    if ($action === 'approve') {
        if ($payment->approvePayment($payment_id, $admin_note)) {
            $success_msg = 'Payment approved successfully!';
            $pmt = $payment->getPaymentWithDetails($payment_id);
        }
    } elseif ($action === 'reject') {
        if ($payment->rejectPayment($payment_id, $admin_note)) {
            $success_msg = 'Payment rejected successfully!';
            $pmt = $payment->getPaymentWithDetails($payment_id);
        }
    }
}

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment #<?php echo $pmt['id']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>User:</strong> <?php echo sanitize($pmt['fullname']); ?></p>
                            <p><strong>Email:</strong> <?php echo sanitize($pmt['email']); ?></p>
                            <p><strong>Invitation:</strong> <?php echo sanitize($pmt['host_name_1']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Package:</strong> <?php echo sanitize($pmt['package_name']); ?></p>
                            <p><strong>Amount:</strong> <span class="h5 text-primary">$<?php echo number_format($pmt['amount'], 2); ?></span></p>
                            <p><strong>Transaction ID:</strong> <code><?php echo sanitize($pmt['transaction_id'] ?? 'N/A'); ?></code></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Payment Action:</strong> <span class="badge bg-secondary"><?php echo ucfirst($pmt['payment_action']); ?></span></p>
                            <p><strong>Status:</strong>
                                <span class="badge badge-<?php echo $pmt['payment_status'] === 'approved' ? 'success' : ($pmt['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($pmt['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created:</strong> <?php echo formatDate($pmt['created_at'], 'F j, Y H:i A'); ?></p>
                            <p><strong>Updated:</strong> <?php echo formatDate($pmt['updated_at'], 'F j, Y H:i A'); ?></p>
                        </div>
                    </div>

                    <?php if (!empty($pmt['admin_note'])): ?>
                        <hr>
                        <p><strong>Admin Note:</strong></p>
                        <p class="text-muted"><?php echo sanitize($pmt['admin_note']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Proof Image -->
            <?php if (!empty($pmt['proof_img'])): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Proof</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="<?php echo APP_URL . '/' . sanitize($pmt['proof_img']); ?>" alt="Payment Proof" class="img-fluid rounded" style="max-height: 500px;">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Sidebar -->
        <div class="col-lg-4">
            <?php if ($pmt['payment_status'] === 'pending'): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Actions</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="admin_note" class="form-label">Admin Note</label>
                                <textarea class="form-control" id="admin_note" name="admin_note" rows="4" placeholder="Add a note for this payment..."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-lg">
                                    <i class="fas fa-check"></i> Approve Payment
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-lg">
                                    <i class="fas fa-times"></i> Reject Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    This payment has already been <?php echo ucfirst($pmt['payment_status']); ?>.
                </div>
            <?php endif; ?>

            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment Status</h5>
                </div>
                <div class="card-body">
                    <p>
                        Status: <span class="badge badge-<?php echo $pmt['payment_status'] === 'approved' ? 'success' : ($pmt['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($pmt['payment_status']); ?>
                        </span>
                    </p>
                    <p class="text-muted small">Last updated: <?php echo getTimeAgo($pmt['updated_at']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

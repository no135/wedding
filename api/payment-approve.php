<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Invitation.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';

if (empty($payment_id)) {
    sendResponse(false, 'Invalid payment ID', null, 400);
}

$payment = new Payment();
$invitation = new Invitation();
$db = new Database();

// Get payment
$pmt = $payment->getPaymentWithDetails($payment_id);

if (!$pmt) {
    sendResponse(false, 'Payment not found', null, 404);
}

// Approve payment
if ($payment->approvePayment($payment_id, $admin_note)) {
    // Update invitation status if payment_action is 'buy'
    if ($pmt['payment_action'] === 'buy' && $pmt['invitation_id']) {
        $invitation->updateStatus($pmt['invitation_id'], 'pending_approval');
    }

    sendResponse(true, 'Payment approved successfully');
} else {
    sendResponse(false, 'Failed to approve payment', null, 500);
}

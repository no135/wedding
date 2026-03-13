<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Payment.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';

if (empty($payment_id)) {
    sendResponse(false, 'Invalid payment ID', null, 400);
}

$payment = new Payment();
$pmt = $payment->getPaymentWithDetails($payment_id);

if (!$pmt) {
    sendResponse(false, 'Payment not found', null, 404);
}

// Reject payment
if ($payment->rejectPayment($payment_id, $admin_note)) {
    sendResponse(true, 'Payment rejected successfully');
} else {
    sendResponse(false, 'Failed to reject payment', null, 500);
}

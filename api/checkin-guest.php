<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/RSVP.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$middleware->validateMethod(['GET', 'POST']);

$qr_token = isset($_GET['token']) ? sanitize($_GET['token']) : '';

if (empty($qr_token)) {
    sendResponse(false, 'QR token is required', null, 400);
}

$rsvp = new RSVP();
$guest = $rsvp->getRSVPByQRToken($qr_token);

if (!$guest) {
    sendResponse(false, 'Guest not found', null, 404);
}

if ($guest['attended'] == 1) {
    sendResponse(false, 'Guest already checked in', null, 400);
}

// Check in guest
if ($rsvp->checkInGuest($guest['id'])) {
    sendResponse(true, 'Guest checked in successfully', ['guest' => $guest]);
} else {
    sendResponse(false, 'Failed to check in guest', null, 500);
}

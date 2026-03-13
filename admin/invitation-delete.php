<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Invitation.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$invitation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($invitation_id)) {
    header('Location: ' . APP_URL . '/admin/invitations.php');
    exit;
}

$invitation = new Invitation();
$inv = $invitation->getById($invitation_id);

if (!$inv) {
    header('Location: ' . APP_URL . '/404.php');
    exit;
}

// Delete the invitation (cascading delete handled by database)
if ($invitation->delete($invitation_id)) {
    header('Location: ' . APP_URL . '/admin/invitations.php?deleted=1');
} else {
    header('Location: ' . APP_URL . '/admin/invitation-detail.php?id=' . $invitation_id . '&error=delete-failed');
}
exit;

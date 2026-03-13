<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Invitation.php';
require_once __DIR__ . '/../models/RSVP.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$invitation = new Invitation();
$rsvp = new RSVP();

$invitation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($invitation_id)) {
    sendResponse(false, 'Invalid invitation ID', null, 400);
}

$inv = $invitation->getInvitationWithDetails($invitation_id);

if (!$inv) {
    sendResponse(false, 'Invitation not found', null, 404);
}

// Get additional data
$db = new Database();

$rsvps = $rsvp->getInvitationRSVPs($invitation_id);
$rsvp_stats = $rsvp->getInvitationGuestStats($invitation_id);

$media = $db->fetchAll("SELECT * FROM media WHERE invitation_id = ? ORDER BY created_at DESC", [$invitation_id]);
$locations = $db->fetchAll("SELECT * FROM locations WHERE invitation_id = ? ORDER BY created_at DESC", [$invitation_id]);
$events = $db->fetchAll("SELECT * FROM invitation_events WHERE invitation_id = ? ORDER BY event_time ASC", [$invitation_id]);
$guestbook = $db->fetchAll("SELECT * FROM guestbook WHERE invitation_id = ? ORDER BY created_at DESC LIMIT 20", [$invitation_id]);

$response = [
    'invitation' => $inv,
    'rsvps' => $rsvps,
    'rsvp_stats' => $rsvp_stats,
    'media' => $media,
    'locations' => $locations,
    'events' => $events,
    'guestbook' => $guestbook
];

sendResponse(true, 'Invitation details retrieved', $response);

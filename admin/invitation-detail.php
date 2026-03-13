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

$page_title = 'Invitation Details';
$invitation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($invitation_id)) {
    header('Location: ' . APP_URL . '/admin/invitations.php');
    exit;
}

$invitation = new Invitation();
$rsvp = new RSVP();

$inv = $invitation->getInvitationWithDetails($invitation_id);

if (!$inv) {
    header('Location: ' . APP_URL . '/404.php');
    exit;
}

$db = new Database();
$rsvps = $rsvp->getInvitationRSVPs($invitation_id);
$rsvp_stats = $rsvp->getInvitationGuestStats($invitation_id);
$media = $db->fetchAll("SELECT * FROM media WHERE invitation_id = ? ORDER BY created_at DESC", [$invitation_id]);
$locations = $db->fetchAll("SELECT * FROM locations WHERE invitation_id = ?", [$invitation_id]);
$events = $db->fetchAll("SELECT * FROM invitation_events WHERE invitation_id = ?", [$invitation_id]);

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Invitation Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h2><?php echo sanitize($inv['host_name_1']); ?> & <?php echo sanitize($inv['host_name_2'] ?? 'Guest'); ?></h2>
                    <p class="text-muted">Created by: <strong><?php echo sanitize($inv['user_fullname']); ?></strong> (<?php echo sanitize($inv['user_email']); ?>)</p>
                    <p><strong>Package:</strong> <?php echo sanitize($inv['package_name']); ?></p>
                    <p><strong>Event Date:</strong> <?php echo formatDate($inv['event_date'], 'F j, Y H:i A'); ?></p>
                    <p>
                        <strong>Status:</strong>
                        <span class="badge badge-<?php echo $inv['status'] === 'active' ? 'success' : ($inv['status'] === 'pending_approval' ? 'info' : 'warning'); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $inv['status'])); ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?php echo APP_URL; ?>/admin/invitation-edit.php?id=<?php echo $invitation_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="<?php echo APP_URL; ?>/public/invitation.php?slug=<?php echo sanitize($inv['unique_slug']); ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> View Public
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $rsvp_stats['total_guests'] ?? 0; ?></div>
                <div class="stat-label">Total Guests</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $rsvp_stats['confirmed'] ?? 0; ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $rsvp_stats['checked_in'] ?? 0; ?></div>
                <div class="stat-label">Checked In</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $inv['view_count']; ?></div>
                <div class="stat-label">Page Views</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#guests">Guests (<?php echo count($rsvps); ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#media">Media (<?php echo count($media); ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#locations">Locations (<?php echo count($locations); ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#events">Events (<?php echo count($events); ?>)</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Guests Tab -->
                <div class="tab-pane fade show active" id="guests">
                    <div style="padding-top: 20px;">
                        <?php if (!empty($rsvps)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Guest Name</th>
                                            <th>Side</th>
                                            <th>Attendance</th>
                                            <th>Attendees</th>
                                            <th>Check-in</th>
                                            <th>QR Token</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rsvps as $g): ?>
                                            <tr>
                                                <td><?php echo sanitize($g['guest_name']); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo sanitize($g['guest_side']); ?></span></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $g['attendance_status'] === 'Yes' ? 'success' : 'danger'; ?>">
                                                        <?php echo $g['attendance_status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $g['attendees_count']; ?></td>
                                                <td>
                                                    <?php echo $g['attended'] == 1 ? '<span class="badge bg-success">Checked In</span>' : '<span class="badge bg-warning">Not Checked In</span>'; ?>
                                                </td>
                                                <td><code><?php echo sanitize($g['guest_qr_token'] ?? 'Not Generated'); ?></code></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No guests yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Media Tab -->
                <div class="tab-pane fade" id="media">
                    <div style="padding-top: 20px;">
                        <?php if (!empty($media)): ?>
                            <div class="row">
                                <?php foreach ($media as $m): ?>
                                    <div class="col-md-3 mb-3">
                                        <?php if ($m['media_type'] === 'image'): ?>
                                            <img src="<?php echo APP_URL . '/' . sanitize($m['file_path']); ?>" class="img-fluid rounded" style="max-height: 200px;">
                                        <?php else: ?>
                                            <video style="max-height: 200px; max-width: 100%; border-radius: 8px;">
                                                <source src="<?php echo APP_URL . '/' . sanitize($m['file_path']); ?>">
                                            </video>
                                        <?php endif; ?>
                                        <small class="text-muted"><?php echo formatDate($m['created_at'], 'M d, Y'); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No media uploaded</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Locations Tab -->
                <div class="tab-pane fade" id="locations">
                    <div style="padding-top: 20px;">
                        <?php if (!empty($locations)): ?>
                            <?php foreach ($locations as $loc): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo sanitize($loc['location_name']); ?></h5>
                                        <p class="card-text"><?php echo sanitize($loc['address'] ?? 'N/A'); ?></p>
                                        <?php if ($loc['map_link']): ?>
                                            <a href="<?php echo sanitize($loc['map_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Map</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No locations added</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Events Tab -->
                <div class="tab-pane fade" id="events">
                    <div style="padding-top: 20px;">
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $evt): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo sanitize($evt['event_time']); ?></h5>
                                        <p class="card-text"><?php echo sanitize($evt['event_description'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No events added</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

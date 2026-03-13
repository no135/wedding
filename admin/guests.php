<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/RSVP.php';
require_once __DIR__ . '/../models/Invitation.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Manage Guests & RSVPs';
$rsvp = new RSVP();
$invitation = new Invitation();

// Get filter parameters
$invitation_id = isset($_GET['invitation_id']) ? intval($_GET['invitation_id']) : null;
$attendance = isset($_GET['attendance']) ? $_GET['attendance'] : null;

// Get all invitations for filter
$all_invitations = $invitation->getAllInvitations();

// Get RSVPs with filters
$db = new Database();
$query = "SELECT * FROM rsvps WHERE 1=1";
$params = [];

if ($invitation_id) {
    $query .= " AND invitation_id = ?";
    $params[] = $invitation_id;
}

if ($attendance && in_array($attendance, ['Yes', 'No'])) {
    $query .= " AND attendance_status = ?";
    $params[] = $attendance;
}

$query .= " ORDER BY created_at DESC";
$rsvps = $db->fetchAll($query, $params);

// Get stats
$stats = [
    'total_guests' => count($rsvps),
    'confirmed' => count(array_filter($rsvps, function($r) { return $r['attendance_status'] === 'Yes'; })),
    'declined' => count(array_filter($rsvps, function($r) { return $r['attendance_status'] === 'No'; })),
    'checked_in' => count(array_filter($rsvps, function($r) { return $r['attended'] == 1; }))
];

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $stats['total_guests']; ?></div>
                <div class="stat-label">Total Guests</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle text-success"></i></div>
                <div class="stat-value"><?php echo $stats['confirmed']; ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-times-circle text-danger"></i></div>
                <div class="stat-value"><?php echo $stats['declined']; ?></div>
                <div class="stat-label">Declined</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-door-open text-info"></i></div>
                <div class="stat-value"><?php echo $stats['checked_in']; ?></div>
                <div class="stat-label">Checked In</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <select name="invitation_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Invitations</option>
                        <?php foreach ($all_invitations as $inv): ?>
                            <option value="<?php echo $inv['id']; ?>" <?php echo $invitation_id == $inv['id'] ? 'selected' : ''; ?>>
                                <?php echo sanitize($inv['host_name_1']); ?> - <?php echo formatDate($inv['event_date'], 'M d, Y'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <select name="attendance" class="form-select" onchange="this.form.submit()">
                        <option value="">All Attendance Status</option>
                        <option value="Yes" <?php echo $attendance === 'Yes' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="No" <?php echo $attendance === 'No' ? 'selected' : ''; ?>>Declined</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- RSVPs Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($rsvps)): ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Guest Name</th>
                                <th>Side</th>
                                <th>Status</th>
                                <th>Attendees</th>
                                <th>QR Token</th>
                                <th>Check-in</th>
                                <th>Check-in Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rsvps as $guest): ?>
                                <tr>
                                    <td><strong>#<?php echo $guest['id']; ?></strong></td>
                                    <td><?php echo sanitize($guest['guest_name']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo sanitize($guest['guest_side']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $guest['attendance_status'] === 'Yes' ? 'success' : 'danger';
                                        $status_text = $guest['attendance_status'] === 'Yes' ? 'Confirmed' : 'Declined';
                                        ?>
                                        <span class="badge badge-<?php echo $status; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo $guest['attendees_count']; ?></span></td>
                                    <td>
                                        <?php if ($guest['guest_qr_token']): ?>
                                            <code><?php echo sanitize($guest['guest_qr_token']); ?></code>
                                        <?php else: ?>
                                            <small class="text-muted">Not generated</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($guest['attended'] == 1): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Checked In</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Not Checked In</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($guest['checkin_time']): ?>
                                            <small><?php echo formatDate($guest['checkin_time'], 'M d H:i'); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo APP_URL; ?>/admin/guest-detail.php?id=<?php echo $guest['id']; ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/admin/guest-edit.php?id=<?php echo $guest['id']; ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No guests found
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

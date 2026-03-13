<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Invitation.php';

// Check admin access
$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Manage Invitations';
$invitation = new Invitation();

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;

// Get invitations
$db = new Database();
$conn = $db->getConnection();

$query = "
    SELECT 
        i.*,
        u.fullname,
        p.package_name,
        COUNT(DISTINCT r.id) as total_guests,
        COUNT(DISTINCT CASE WHEN r.attended = 1 THEN r.id END) as attended_guests
    FROM invitations i
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN packages p ON i.package_id = p.id
    LEFT JOIN rsvps r ON i.id = r.invitation_id
";

if ($status_filter && $status_filter !== 'all') {
    $query .= " WHERE i.status = '" . $conn->quote($status_filter) . "'";
}

$query .= " GROUP BY i.id ORDER BY i.created_at DESC";

$invitations = $db->fetchAll($query);

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Filters and Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="pending_payment" <?php echo $status_filter === 'pending_payment' ? 'selected' : ''; ?>>Pending Payment</option>
                            <option value="pending_approval" <?php echo $status_filter === 'pending_approval' ? 'selected' : ''; ?>>Pending Approval</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <a href="<?php echo APP_URL; ?>/admin/invitation-create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Invitation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Invitations Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($invitations)): ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Host Names</th>
                                <th>User</th>
                                <th>Package</th>
                                <th>Status</th>
                                <th>Guests</th>
                                <th>Views</th>
                                <th>Event Date</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invitations as $inv): ?>
                                <tr>
                                    <td><strong>#<?php echo $inv['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo sanitize($inv['host_name_1']); ?></strong>
                                        <?php if ($inv['host_name_2']): ?>
                                            <br><small class="text-muted"><?php echo sanitize($inv['host_name_2']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo sanitize($inv['fullname']); ?></small><br>
                                        <small class="text-muted"><?php echo sanitize($inv['email'] ?? 'N/A'); ?></small>
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
                                    <td>
                                        <small><?php echo $inv['total_guests']; ?> total</small><br>
                                        <small class="text-success"><?php echo $inv['attended_guests']; ?> attended</small>
                                    </td>
                                    <td><?php echo $inv['view_count']; ?></td>
                                    <td><small><?php echo formatDate($inv['event_date'], 'M d, Y H:i'); ?></small></td>
                                    <td><small><?php echo getTimeAgo($inv['created_at']); ?></small></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo APP_URL; ?>/admin/invitation-detail.php?id=<?php echo $inv['id']; ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/admin/invitation-edit.php?id=<?php echo $inv['id']; ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/admin/invitation-delete.php?id=<?php echo $inv['id']; ?>" class="btn btn-outline-danger" title="Delete" data-action="delete">
                                                <i class="fas fa-trash"></i>
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
                    <i class="fas fa-info-circle"></i> No invitations found
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

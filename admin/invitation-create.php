<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Invitation.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Create Invitation';

// Get packages and types for form
$db = new Database();
$packages = $db->fetchAll("SELECT * FROM packages ORDER BY price ASC");
$invitation_types = $db->fetchAll("SELECT * FROM invitation_type ORDER BY type_name ASC");

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $_SESSION['user_id'];
    $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
    $type_id = isset($_POST['type_id']) ? intval($_POST['type_id']) : 0;
    $host_name_1 = isset($_POST['host_name_1']) ? trim($_POST['host_name_1']) : '';
    $host_name_2 = isset($_POST['host_name_2']) ? trim($_POST['host_name_2']) : '';
    $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : '';

    if (empty($host_name_1) || empty($event_date) || empty($package_id) || empty($type_id)) {
        $error = 'Please fill in all required fields';
    } else {
        // Generate unique slug
        do {
            $slug = generateSlug($host_name_1) . '-' . date('Y') . '-' . bin2hex(random_bytes(3));
            $existing = $db->fetch("SELECT id FROM invitations WHERE unique_slug = ?", [$slug]);
        } while ($existing);

        $data = [
            'user_id' => $user_id,
            'package_id' => $package_id,
            'type_id' => $type_id,
            'host_name_1' => $host_name_1,
            'host_name_2' => $host_name_2,
            'event_date' => $event_date,
            'unique_slug' => $slug,
            'status' => 'pending_approval'
        ];

        $invitation = new Invitation();
        if ($invitation->insert($data)) {
            $success = 'Invitation created successfully!';
            $new_id = $db->lastInsertId();
            header('refresh:2;url=' . APP_URL . '/admin/invitation-detail.php?id=' . $new_id);
        } else {
            $error = 'Failed to create invitation';
        }
    }
}

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header bg-light">
                    <h4 class="mb-0">Create New Invitation</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="host_name_1" class="form-label">Host Name 1 *</label>
                                <input type="text" class="form-control" id="host_name_1" name="host_name_1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="host_name_2" class="form-label">Host Name 2 (Optional)</label>
                                <input type="text" class="form-control" id="host_name_2" name="host_name_2">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type_id" class="form-label">Invitation Type *</label>
                                <select class="form-select" id="type_id" name="type_id" required>
                                    <option value="">Select a type...</option>
                                    <?php foreach ($invitation_types as $type): ?>
                                        <option value="<?php echo $type['id']; ?>"><?php echo sanitize($type['type_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="package_id" class="form-label">Package *</label>
                                <select class="form-select" id="package_id" name="package_id" required>
                                    <option value="">Select a package...</option>
                                    <?php foreach ($packages as $pkg): ?>
                                        <option value="<?php echo $pkg['id']; ?>"><?php echo sanitize($pkg['package_name']); ?> - $<?php echo number_format($pkg['price'], 2); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="event_date" class="form-label">Event Date & Time *</label>
                            <input type="datetime-local" class="form-control" id="event_date" name="event_date" required>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Invitation
                            </button>
                            <a href="<?php echo APP_URL; ?>/admin/invitations.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

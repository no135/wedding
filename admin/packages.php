<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Manage Packages';

// Get all packages
$db = new Database();
$query = "SELECT * FROM packages ORDER BY price ASC";
$packages = $db->fetchAll($query);

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Actions -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5>Total Packages: <span class="text-primary"><?php echo count($packages); ?></span></h5>
            </div>
            <a href="<?php echo APP_URL; ?>/admin/package-create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Package
            </a>
        </div>
    </div>

    <!-- Packages Grid -->
    <div class="row">
        <?php foreach ($packages as $pkg): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><?php echo sanitize($pkg['package_name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="h4 text-primary mb-3">$<?php echo number_format($pkg['price'], 2); ?></p>
                        
                        <h6 class="mb-2">Features:</h6>
                        <ul class="list-unstyled small">
                            <li><?php echo $pkg['max_photos']; ?> Photos</li>
                            <li><?php echo $pkg['max_maps']; ?> Map<?php echo $pkg['max_maps'] > 1 ? 's' : ''; ?></li>
                            <li><?php echo $pkg['has_basic_info'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Basic Info</li>
                            <li><?php echo $pkg['has_music'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Music</li>
                            <li><?php echo $pkg['has_countdown'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Countdown</li>
                            <li><?php echo $pkg['has_timeline'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Timeline</li>
                            <li><?php echo $pkg['has_rsvp_form'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> RSVP Form</li>
                            <li><?php echo $pkg['has_video'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Video</li>
                            <li><?php echo $pkg['has_profile'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Profile</li>
                            <li><?php echo $pkg['has_gift_ideas'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Gift Ideas</li>
                            <li><?php echo $pkg['has_analysis'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Analytics</li>
                            <li><?php echo $pkg['has_qr_code'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> QR Codes</li>
                            <li><?php echo $pkg['has_live_checkin'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?> Live Check-in</li>
                        </ul>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="btn-group w-100" role="group">
                            <a href="<?php echo APP_URL; ?>/admin/package-edit.php?id=<?php echo $pkg['id']; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="<?php echo APP_URL; ?>/admin/package-delete.php?id=<?php echo $pkg['id']; ?>" class="btn btn-outline-danger" data-action="delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

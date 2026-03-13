<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Middleware.php';
require_once __DIR__ . '/../config/Helpers.php';

$middleware = new Middleware();
$middleware->requireAdmin();

$page_title = 'Manage Themes';

// Get all themes
$db = new Database();
$query = "SELECT t.*, p.package_name FROM themes t LEFT JOIN packages p ON t.package_id = p.id ORDER BY t.theme_name ASC";
$themes = $db->fetchAll($query);

include __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <!-- Actions -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5>Total Themes: <span class="text-primary"><?php echo count($themes); ?></span></h5>
            </div>
            <a href="<?php echo APP_URL; ?>/admin/theme-create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Theme
            </a>
        </div>
    </div>

    <!-- Themes Grid -->
    <div class="row">
        <?php foreach ($themes as $theme): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <!-- Color Preview -->
                        <div class="mb-3">
                            <div class="d-flex gap-2">
                                <div style="width: 60px; height: 60px; background-color: <?php echo sanitize($theme['primary_color']); ?>; border-radius: 8px; border: 2px solid #ddd;" title="Primary"></div>
                                <div style="width: 60px; height: 60px; background-color: <?php echo sanitize($theme['secondary_color']); ?>; border-radius: 8px; border: 2px solid #ddd;" title="Secondary"></div>
                                <div style="width: 60px; height: 60px; background-color: <?php echo sanitize($theme['bg_color']); ?>; border-radius: 8px; border: 2px solid #ddd;" title="Background"></div>
                                <div style="width: 60px; height: 60px; background-color: <?php echo sanitize($theme['text_color']); ?>; border-radius: 8px; border: 2px solid #ddd;" title="Text"></div>
                            </div>
                        </div>

                        <h5 class="mb-2"><?php echo sanitize($theme['theme_name']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo sanitize($theme['package_name'] ?? 'General'); ?></p>

                        <table class="table table-sm">
                            <tr>
                                <td><strong>Primary:</strong></td>
                                <td><code><?php echo sanitize($theme['primary_color']); ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Secondary:</strong></td>
                                <td><code><?php echo sanitize($theme['secondary_color']); ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Background:</strong></td>
                                <td><code><?php echo sanitize($theme['bg_color']); ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Text:</strong></td>
                                <td><code><?php echo sanitize($theme['text_color']); ?></code></td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="btn-group w-100" role="group">
                            <a href="<?php echo APP_URL; ?>/admin/theme-edit.php?id=<?php echo $theme['id']; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="<?php echo APP_URL; ?>/admin/theme-delete.php?id=<?php echo $theme['id']; ?>" class="btn btn-outline-danger" data-action="delete">
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

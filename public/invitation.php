<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Helpers.php';
require_once __DIR__ . '/../models/Invitation.php';
require_once __DIR__ . '/../models/RSVP.php';

$invitation = new Invitation();
$rsvp_model = new RSVP();

// Get slug from URL
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: ' . APP_URL . '/404.php');
    exit;
}

// Get invitation
$inv_data = $invitation->getInvitationBySlug($slug);

if (!$inv_data) {
    header('Location: ' . APP_URL . '/404.php');
    exit;
}

// Increment view count
$invitation->incrementViewCount($inv_data['id']);

// Get invitation details
$db = new Database();
$query = "
    SELECT 
        i.*,
        u.fullname as user_fullname,
        u.email as user_email,
        p.package_name,
        it.type_name,
        it.label_1,
        it.label_2,
        COUNT(DISTINCT r.id) as total_guests,
        COUNT(DISTINCT CASE WHEN r.attendance_status = 'Yes' THEN r.id END) as confirmed_guests
    FROM invitations i
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN packages p ON i.package_id = p.id
    LEFT JOIN invitation_type it ON i.type_id = it.id
    LEFT JOIN rsvps r ON i.id = r.invitation_id
    WHERE i.unique_slug = ?
    GROUP BY i.id
";
$inv = $db->fetch($query, [$slug]);

// Get media
$media = $db->fetchAll("SELECT * FROM media WHERE invitation_id = ? ORDER BY created_at DESC", [$inv['id']]);

// Get locations
$locations = $db->fetchAll("SELECT l.*, lt.type_name FROM locations l LEFT JOIN location_types lt ON l.location_type_id = lt.id WHERE l.invitation_id = ? ORDER BY l.created_at DESC", [$inv['id']]);

// Get events
$events = $db->fetchAll("SELECT ie.*, e.event_title FROM invitation_events ie LEFT JOIN events e ON ie.event_id = e.id WHERE ie.invitation_id = ? ORDER BY ie.event_time ASC", [$inv['id']]);

// Get guestbook messages
$messages = $db->fetchAll("SELECT * FROM guestbook WHERE invitation_id = ? ORDER BY created_at DESC", [$inv['id']]);

// Get theme and font
$theme_data = $db->fetch("SELECT * FROM user_themes WHERE invitation_id = ?", [$inv['id']]);

// Handle RSVP submission
$rsvp_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rsvp') {
    $guest_name = isset($_POST['guest_name']) ? trim($_POST['guest_name']) : '';
    $guest_side = isset($_POST['guest_side']) ? $_POST['guest_side'] : 'General';
    $attendance = isset($_POST['attendance']) ? $_POST['attendance'] : 'Yes';
    $attendees_count = isset($_POST['attendees_count']) ? intval($_POST['attendees_count']) : 1;

    if (!empty($guest_name)) {
        $rsvp_data = [
            'invitation_id' => $inv['id'],
            'guest_name' => $guest_name,
            'guest_side' => $guest_side,
            'attendance_status' => $attendance,
            'attendees_count' => $attendees_count
        ];
        if ($rsvp_model->insert($rsvp_data)) {
            $rsvp_success = true;
            // Refresh data
            $inv = $db->fetch($query, [$slug]);
        }
    }
}

// Get theme colors
$primary = $theme_data['theme_id'] ? $db->fetch("SELECT primary_color FROM themes WHERE id = ?", [$theme_data['theme_id']])['primary_color'] : '#667eea';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($inv['host_name_1']); ?> & <?php echo sanitize($inv['host_name_2'] ?? 'Guest'); ?>'s Wedding</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?php echo $primary; ?>;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 24px;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .hero-date {
            font-size: 18px;
            margin: 20px 0;
        }

        .section {
            padding: 60px 20px;
            border-bottom: 1px solid #eee;
        }

        .section h2 {
            font-size: 36px;
            margin-bottom: 30px;
            color: var(--primary-color);
            text-align: center;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .gallery-item img, .gallery-item video {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .gallery-item:hover img, .gallery-item:hover video {
            transform: scale(1.05);
        }

        .event-item {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .event-time {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .location-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .location-name {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .rsvp-form {
            background: #f9fafb;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: 0 auto;
        }

        .rsvp-form .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .rsvp-form .form-control, .rsvp-form .form-select {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 15px;
        }

        .rsvp-form .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .rsvp-form .btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .message-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .message-name {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .message-text {
            color: #666;
            line-height: 1.6;
        }

        .message-date {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }

        footer {
            background: #1f2937;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .alert {
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 18px;
            }

            .section h2 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1><i class="fas fa-heart"></i> <?php echo sanitize($inv['host_name_1']); ?></h1>
            <p>&</p>
            <h1><?php echo sanitize($inv['host_name_2'] ?? 'Our Guest'); ?></h1>
            <div class="hero-date">
                <i class="fas fa-calendar"></i> <?php echo formatDate($inv['event_date'], 'F j, Y'); ?>
                <br>
                <small><?php echo formatDate($inv['event_date'], 'g:i A'); ?></small>
            </div>
            <p style="margin-top: 30px; font-size: 16px;">We are delighted to invite you to celebrate our special day with us</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="padding-top: 40px; padding-bottom: 40px;">
        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <div class="stat-value"><?php echo $inv['total_guests']; ?></div>
                <div class="stat-label">Total Guests</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?php echo $inv['confirmed_guests']; ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?php echo $inv['view_count']; ?></div>
                <div class="stat-label">Views</div>
            </div>
        </div>

        <!-- Gallery -->
        <?php if (!empty($media)): ?>
            <section class="section">
                <h2><i class="fas fa-images"></i> Gallery</h2>
                <div class="gallery">
                    <?php foreach ($media as $m): ?>
                        <div class="gallery-item">
                            <?php if ($m['media_type'] === 'image'): ?>
                                <img src="<?php echo APP_URL . '/' . sanitize($m['file_path']); ?>" alt="Gallery">
                            <?php else: ?>
                                <video controls style="width: 100%; height: 250px; object-fit: cover;">
                                    <source src="<?php echo APP_URL . '/' . sanitize($m['file_path']); ?>">
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Events Timeline -->
        <?php if (!empty($events)): ?>
            <section class="section">
                <h2><i class="fas fa-clock"></i> Event Timeline</h2>
                <?php foreach ($events as $evt): ?>
                    <div class="event-item">
                        <div class="event-time"><i class="fas fa-star"></i> <?php echo sanitize($evt['event_time']); ?></div>
                        <div><strong><?php echo sanitize($evt['event_title']); ?></strong></div>
                        <?php if ($evt['event_description']): ?>
                            <p class="text-muted small" style="margin-top: 8px;"><?php echo sanitize($evt['event_description']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <!-- Locations -->
        <?php if (!empty($locations)): ?>
            <section class="section">
                <h2><i class="fas fa-map-marker-alt"></i> Locations</h2>
                <?php foreach ($locations as $loc): ?>
                    <div class="location-item">
                        <div class="location-name">
                            <i class="fas fa-map-pin"></i> <?php echo sanitize($loc['location_name']); ?>
                        </div>
                        <?php if ($loc['address']): ?>
                            <p><?php echo sanitize($loc['address']); ?></p>
                        <?php endif; ?>
                        <?php if ($loc['map_link']): ?>
                            <a href="<?php echo sanitize($loc['map_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-directions"></i> View on Map
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <!-- RSVP Section -->
        <section class="section">
            <h2><i class="fas fa-check-circle"></i> RSVP</h2>
            <?php if ($rsvp_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check"></i> Thank you for your RSVP! We look forward to celebrating with you.
                </div>
            <?php endif; ?>
            <form method="POST" class="rsvp-form">
                <input type="hidden" name="action" value="rsvp">
                <div>
                    <label for="guest_name" class="form-label">Your Name *</label>
                    <input type="text" class="form-control" id="guest_name" name="guest_name" required>
                </div>
                <div>
                    <label for="guest_side" class="form-label"><?php echo sanitize($inv['label_1'] ?? 'Side 1'); ?> or <?php echo sanitize($inv['label_2'] ?? 'Side 2'); ?>?</label>
                    <select class="form-select" id="guest_side" name="guest_side">
                        <option value="General">General</option>
                        <option value="<?php echo sanitize($inv['label_1'] ?? 'Side 1'); ?>"><?php echo sanitize($inv['label_1'] ?? 'Side 1'); ?></option>
                        <option value="<?php echo sanitize($inv['label_2'] ?? 'Side 2'); ?>"><?php echo sanitize($inv['label_2'] ?? 'Side 2'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="attendance" class="form-label">Will you be attending?</label>
                    <select class="form-select" id="attendance" name="attendance">
                        <option value="Yes">Yes, I'll be there!</option>
                        <option value="No">No, I won't be able to attend</option>
                    </select>
                </div>
                <div>
                    <label for="attendees_count" class="form-label">Number of Guests (including yourself)</label>
                    <input type="number" class="form-control" id="attendees_count" name="attendees_count" min="1" value="1">
                </div>
                <button type="submit" class="btn w-100">
                    <i class="fas fa-paper-plane"></i> Submit RSVP
                </button>
            </form>
        </section>

        <!-- Guestbook -->
        <section class="section">
            <h2><i class="fas fa-book"></i> Guestbook</h2>
            <?php if (!empty($messages)): ?>
                <div style="margin-bottom: 30px;">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item">
                            <div class="message-name"><?php echo sanitize($msg['guest_name']); ?></div>
                            <div class="message-text"><?php echo sanitize($msg['message']); ?></div>
                            <div class="message-date"><?php echo getTimeAgo($msg['created_at']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($inv['allow_comments']): ?>
                <form method="POST" style="background: #f9fafb; padding: 20px; border-radius: 10px;">
                    <input type="hidden" name="action" value="message">
                    <div class="form-group">
                        <label for="msg_name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="msg_name" name="msg_name" required>
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn" style="background-color: var(--primary-color); color: white;">
                        <i class="fas fa-comment"></i> Leave a Message
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
